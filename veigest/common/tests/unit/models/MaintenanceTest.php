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

        $this->assertFalse($maintenance->validate(), 'Maintenance sem dados não deve validar');
        $this->assertArrayHasKey('company_id', $maintenance->errors, 'Company_id deve ser obrigatório');
        $this->assertArrayHasKey('vehicle_id', $maintenance->errors, 'Vehicle_id deve ser obrigatório');
        $this->assertArrayHasKey('type', $maintenance->errors, 'Type deve ser obrigatório');
        $this->assertArrayHasKey('date', $maintenance->errors, 'Date deve ser obrigatório');
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

        $this->assertFalse($maintenance->validate(), 'Status inválido não deve validar');
        $this->assertArrayHasKey('status', $maintenance->errors, 'Deve ter erro no status');

        // Testar status válidos
        $validStatuses = [
            Maintenance::STATUS_SCHEDULED,
            Maintenance::STATUS_COMPLETED,
            Maintenance::STATUS_CANCELLED
        ];
        foreach ($validStatuses as $status) {
            $maintenance->status = $status;
            $this->assertTrue($maintenance->validate(['status']), "Status '$status' deve ser válido");
        }
    }

    /**
     * Teste 3: Constantes de status
     * Verifica se as constantes estão definidas corretamente
     */
    public function testConstants()
    {
        $this->assertEquals('scheduled', Maintenance::STATUS_SCHEDULED, 'STATUS_SCHEDULED deve ser "scheduled"');
        $this->assertEquals('completed', Maintenance::STATUS_COMPLETED, 'STATUS_COMPLETED deve ser "completed"');
        $this->assertEquals('cancelled', Maintenance::STATUS_CANCELLED, 'STATUS_CANCELLED deve ser "cancelled"');
    }

    /**
     * Teste 4: Método getTypes retorna tipos válidos
     * Verifica se getTypes retorna array com tipos de manutenção
     */
    public function testGetTypes()
    {
        $types = Maintenance::getTypes();
        
        $this->assertIsArray($types, 'getTypes deve retornar array');
        $this->assertGreaterThan(5, count($types), 'getTypes deve ter múltiplas opções');
        $this->assertArrayHasKey('Óleo', $types, 'getTypes deve conter "Óleo"');
        $this->assertArrayHasKey('Pneus', $types, 'getTypes deve conter "Pneus"');
        $this->assertArrayHasKey('Inspeção', $types, 'getTypes deve conter "Inspeção"');
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

        $this->assertTrue($maintenance->validate(['date']), 'Data válida deve passar validação');

        // Data inválida
        $maintenance->date = 'data_invalida';
        $this->assertFalse($maintenance->validate(['date']), 'Data inválida não deve validar');
    }

    /**
     * Teste 6: Validação de custo (número)
     * Verifica se custo aceita apenas valores numéricos
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

        $this->assertTrue($maintenance->validate(['cost']), 'Custo numérico válido deve passar');
        
        $maintenance->cost = 'nao_numerico';
        $this->assertFalse($maintenance->validate(['cost']), 'Custo não numérico não deve validar');
    }

    /**
     * Teste 7: Aliases PT -> EN
     * Verifica se os aliases em português funcionam corretamente
     */
    public function testPTAliases()
    {
        $maintenance = new Maintenance();
        
        // Atribuir valores via aliases PT
        $maintenance->tipo = 'Óleo';
        $maintenance->descricao = 'Mudança de óleo';
        $maintenance->data = '2025-02-01';
        $maintenance->custo = 75.00;
        $maintenance->km_registro = 55000;
        $maintenance->oficina = 'Oficina Test';

        // Verificar que os valores foram atribuídos aos campos EN
        $this->assertEquals('Óleo', $maintenance->type, 'Alias tipo deve mapear para type');
        $this->assertEquals('Mudança de óleo', $maintenance->description, 'Alias descricao deve mapear para description');
        $this->assertEquals('2025-02-01', $maintenance->date, 'Alias data deve mapear para date');
        $this->assertEquals(75.00, $maintenance->cost, 'Alias custo deve mapear para cost');
        $this->assertEquals(55000, $maintenance->mileage_record, 'Alias km_registro deve mapear para mileage_record');
        $this->assertEquals('Oficina Test', $maintenance->workshop, 'Alias oficina deve mapear para workshop');
        
        // Testar getters PT
        $this->assertEquals('Óleo', $maintenance->tipo, 'Getter tipo deve retornar type');
        $this->assertEquals('Mudança de óleo', $maintenance->descricao, 'Getter descricao deve retornar description');
        $this->assertEquals('2025-02-01', $maintenance->data, 'Getter data deve retornar date');
    }

    /**
     * Teste 8: Relação com Vehicle
     * Verifica se a relação getVehicle funciona
     */
    public function testVehicleRelation()
    {
        // Buscar manutenção existente do fixture
        $maintenance = Maintenance::findOne(1);
        
        $this->assertNotNull($maintenance, 'Manutenção deve existir');
        
        $vehicle = $maintenance->vehicle;
        $this->assertNotNull($vehicle, 'Vehicle deve ser encontrado através da relação');
        $this->assertInstanceOf(Vehicle::class, $vehicle, 'Vehicle deve ser instância de Vehicle');
        $this->assertEquals($maintenance->vehicle_id, $vehicle->id, 'Vehicle ID deve corresponder');
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

        $this->assertFalse($maintenance->validate(), 'Vehicle inexistente não deve validar');
        $this->assertArrayHasKey('vehicle_id', $maintenance->errors, 'Deve ter erro no vehicle_id');

        // Com vehicle_id válido
        $maintenance->vehicle_id = 1; // ID do fixture
        $this->assertTrue($maintenance->validate(['vehicle_id']), 'Vehicle existente deve validar');
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

        $this->assertTrue($maintenance->save(), 'CREATE: Maintenance deve ser salvo');
        $this->assertNotNull($maintenance->id, 'CREATE: ID deve ser atribuído');
        $maintenanceId = $maintenance->id;

        // READ
        $foundMaintenance = Maintenance::findOne($maintenanceId);
        $this->assertNotNull($foundMaintenance, 'READ: Maintenance deve ser encontrado');
        $this->assertEquals('CRUD Test Type', $foundMaintenance->type, 'READ: Type deve corresponder');
        $this->assertEquals(1, $foundMaintenance->vehicle_id, 'READ: Vehicle_id deve corresponder');

        // UPDATE
        $foundMaintenance->status = Maintenance::STATUS_COMPLETED;
        $foundMaintenance->cost = 125.50;
        $this->assertTrue($foundMaintenance->save(), 'UPDATE: Maintenance deve ser atualizado');

        $updatedMaintenance = Maintenance::findOne($maintenanceId);
        $this->assertEquals('completed', $updatedMaintenance->status, 'UPDATE: Status atualizado deve corresponder');
        $this->assertEquals(125.50, $updatedMaintenance->cost, 'UPDATE: Cost atualizado deve corresponder');

        // DELETE
        $this->assertEquals(1, $updatedMaintenance->delete(), 'DELETE: Maintenance deve ser eliminado');
        
        $deletedMaintenance = Maintenance::findOne($maintenanceId);
        $this->assertNull($deletedMaintenance, 'DELETE: Maintenance não deve existir após eliminar');
    }
}
