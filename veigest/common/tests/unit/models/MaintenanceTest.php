<?php

namespace common\tests\unit\models;

use Yii;
use frontend\models\Maintenance;
use frontend\models\Vehicle;
use common\fixtures\CompanyFixture;
use common\fixtures\VehicleFixture;
use common\fixtures\MaintenanceFixture;

/**
 * Testes Unitários - Maintenance Model
 * 
 * Testa validação, constantes e métodos do modelo Maintenance.
 * Segue padrão Active Record: Create, Read, Update, Delete
 * 
 * @group unit
 * @group models
 * @group maintenance
 */
class MaintenanceTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function _fixtures()
    {
        return [
            'company' => [
                'class' => CompanyFixture::class,
                'dataFile' => codecept_data_dir() . 'company.php'
            ],
            'vehicle' => [
                'class' => VehicleFixture::class,
                'dataFile' => codecept_data_dir() . 'vehicle.php'
            ],
            'maintenance' => [
                'class' => MaintenanceFixture::class,
                'dataFile' => codecept_data_dir() . 'maintenance.php'
            ],
        ];
    }

    /**
     * Teste 1: Validação de campos obrigatórios
     * Verifica se company_id, vehicle_id, type e date são obrigatórios
     */
    public function testValidationRequired()
    {
        $maintenance = new Maintenance();

        verify('Maintenance sem dados não deve validar', $maintenance->validate())->false();
        verify('Company_id deve ser obrigatório', $maintenance->errors)->arrayHasKey('company_id');
        verify('Vehicle_id deve ser obrigatório', $maintenance->errors)->arrayHasKey('vehicle_id');
        verify('Type deve ser obrigatório', $maintenance->errors)->arrayHasKey('type');
        verify('Date deve ser obrigatório', $maintenance->errors)->arrayHasKey('date');
    }

    /**
     * Teste 2: Validação de status
     * Verifica se status inválidos são rejeitados
     */
    public function testValidationStatus()
    {
        $maintenance = new Maintenance([
            'company_id' => 1,
            'vehicle_id' => 1,
            'type' => 'Óleo',
            'date' => '2025-01-15',
            'status' => 'status_invalido',
        ]);

        verify('Status inválido não deve validar', $maintenance->validate())->false();
        verify('Deve ter erro no status', $maintenance->errors)->arrayHasKey('status');

        // Testar status válidos
        $validStatuses = [
            Maintenance::STATUS_SCHEDULED,
            Maintenance::STATUS_COMPLETED,
            Maintenance::STATUS_CANCELLED
        ];
        foreach ($validStatuses as $status) {
            $maintenance->status = $status;
            verify("Status '$status' deve ser válido", $maintenance->validate(['status']))->true();
        }
    }

    /**
     * Teste 3: Constantes de status
     * Verifica se as constantes estão definidas corretamente
     */
    public function testConstants()
    {
        verify('STATUS_SCHEDULED deve ser "scheduled"', Maintenance::STATUS_SCHEDULED)->equals('scheduled');
        verify('STATUS_COMPLETED deve ser "completed"', Maintenance::STATUS_COMPLETED)->equals('completed');
        verify('STATUS_CANCELLED deve ser "cancelled"', Maintenance::STATUS_CANCELLED)->equals('cancelled');
    }

    /**
     * Teste 4: Método getTypes retorna tipos válidos
     * Verifica se getTypes retorna array com tipos de manutenção
     */
    public function testGetTypes()
    {
        $types = Maintenance::getTypes();
        
        verify('getTypes deve retornar array', is_array($types))->true();
        verify('getTypes deve ter múltiplas opções', count($types))->greaterThan(5);
        verify('getTypes deve conter "Óleo"', $types)->arrayHasKey('Óleo');
        verify('getTypes deve conter "Pneus"', $types)->arrayHasKey('Pneus');
        verify('getTypes deve conter "Inspeção"', $types)->arrayHasKey('Inspeção');
    }

    /**
     * Teste 5: Validação de formato de data
     * Verifica se datas inválidas são rejeitadas
     */
    public function testValidationDateFormat()
    {
        $maintenance = new Maintenance([
            'company_id' => 1,
            'vehicle_id' => 1,
            'type' => 'Óleo',
            'date' => '2025-01-15', // formato válido Y-m-d
            'status' => 'scheduled',
        ]);

        verify('Data válida deve passar validação', $maintenance->validate(['date']))->true();

        // Data inválida
        $maintenance->date = 'data_invalida';
        verify('Data inválida não deve validar', $maintenance->validate(['date']))->false();
    }

    /**
     * Teste 6: Validação de custo (número)
     * Verifica se cost aceita apenas valores numéricos
     */
    public function testValidationCost()
    {
        $maintenance = new Maintenance([
            'company_id' => 1,
            'vehicle_id' => 1,
            'type' => 'Óleo',
            'date' => '2025-01-15',
            'status' => 'scheduled',
            'cost' => 150.50,
        ]);

        verify('Custo numérico válido deve passar', $maintenance->validate(['cost']))->true();
        
        $maintenance->cost = 'nao_numerico';
        verify('Custo não numérico não deve validar', $maintenance->validate(['cost']))->false();
    }

    /**
     * Teste 7: Aliases PT-EN
     * Verifica se os aliases em português funcionam
     */
    public function testPTAliases()
    {
        $maintenance = new Maintenance();
        
        // Testar setters
        $maintenance->tipo = 'Óleo';
        $maintenance->descricao = 'Mudança de óleo';
        $maintenance->data = '2025-02-01';
        $maintenance->custo = 75.00;
        $maintenance->km_registro = 55000;
        $maintenance->oficina = 'Oficina Test';

        // Testar getters
        verify('Alias tipo deve mapear para type', $maintenance->type)->equals('Óleo');
        verify('Alias descricao deve mapear para description', $maintenance->description)->equals('Mudança de óleo');
        verify('Alias data deve mapear para date', $maintenance->date)->equals('2025-02-01');
        verify('Alias custo deve mapear para cost', $maintenance->cost)->equals(75.00);
        verify('Alias km_registro deve mapear para mileage_record', $maintenance->mileage_record)->equals(55000);
        verify('Alias oficina deve mapear para workshop', $maintenance->workshop)->equals('Oficina Test');
    }

    /**
     * Teste 8: Relação com Vehicle
     * Verifica se a relação getVehicle funciona
     */
    public function testVehicleRelation()
    {
        // Buscar manutenção existente do fixture
        $maintenance = Maintenance::findOne(1);
        
        verify('Manutenção deve existir', $maintenance)->notNull();
        
        $vehicle = $maintenance->vehicle;
        verify('Vehicle deve ser encontrado através da relação', $vehicle)->notNull();
        verify('Vehicle deve ser instância de Vehicle', $vehicle)->isInstanceOf(Vehicle::class);
        verify('Vehicle ID deve corresponder', $vehicle->id)->equals($maintenance->vehicle_id);
    }

    /**
     * Teste 9: Validação de vehicle_id existente
     * Verifica se vehicle_id referencia veículo existente
     */
    public function testVehicleExists()
    {
        $maintenance = new Maintenance([
            'company_id' => 1,
            'vehicle_id' => 99999, // ID que não existe
            'type' => 'Óleo',
            'date' => '2025-01-15',
            'status' => 'scheduled',
        ]);

        verify('Vehicle inexistente não deve validar', $maintenance->validate())->false();
        verify('Deve ter erro no vehicle_id', $maintenance->errors)->arrayHasKey('vehicle_id');

        // Com vehicle_id válido
        $maintenance->vehicle_id = 1; // ID do fixture
        verify('Vehicle existente deve validar', $maintenance->validate(['vehicle_id']))->true();
    }

    /**
     * Teste 10: CRUD completo
     * Testa integração completa com banco de dados
     */
    public function testCRUDOperations()
    {
        // CREATE
        $maintenance = new Maintenance([
            'company_id' => 1,
            'vehicle_id' => 1,
            'type' => 'CRUD Test Type',
            'description' => 'Test maintenance for CRUD',
            'date' => '2025-03-01',
            'status' => Maintenance::STATUS_SCHEDULED,
            'cost' => 100.00,
            'mileage_record' => 60000,
            'workshop' => 'CRUD Workshop',
        ]);

        verify('CREATE: Maintenance deve ser salvo', $maintenance->save())->true();
        verify('CREATE: ID deve ser atribuído', $maintenance->id)->notNull();
        $maintenanceId = $maintenance->id;

        // READ
        $foundMaintenance = Maintenance::findOne($maintenanceId);
        verify('READ: Maintenance deve ser encontrado', $foundMaintenance)->notNull();
        verify('READ: Type deve corresponder', $foundMaintenance->type)->equals('CRUD Test Type');
        verify('READ: Vehicle_id deve corresponder', $foundMaintenance->vehicle_id)->equals(1);

        // UPDATE
        $foundMaintenance->status = Maintenance::STATUS_COMPLETED;
        $foundMaintenance->cost = 125.50;
        verify('UPDATE: Maintenance deve ser atualizado', $foundMaintenance->save())->true();

        $updatedMaintenance = Maintenance::findOne($maintenanceId);
        verify('UPDATE: Status atualizado deve corresponder', $updatedMaintenance->status)->equals('completed');
        verify('UPDATE: Cost atualizado deve corresponder', $updatedMaintenance->cost)->equals(125.50);

        // DELETE
        verify('DELETE: Maintenance deve ser eliminado', $updatedMaintenance->delete())->equals(1);
        
        $deletedMaintenance = Maintenance::findOne($maintenanceId);
        verify('DELETE: Maintenance não deve existir após eliminar', $deletedMaintenance)->null();
    }
}
