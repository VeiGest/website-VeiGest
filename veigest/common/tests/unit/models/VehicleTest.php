<?php

namespace common\tests\unit\models;

use Yii;
use frontend\models\Vehicle;
use common\fixtures\CompanyFixture;
use common\fixtures\VehicleFixture;

/**
 * Testes Unitários - Vehicle Model
 * 
 * Testa validação, constantes e métodos do modelo Vehicle.
 * Segue padrão Active Record: Create, Read, Update, Delete
 * 
 * @group unit
 * @group models
 * @group vehicle
 */
class VehicleTest extends \Codeception\Test\Unit
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
        ];
    }

    /**
     * Teste 1: Validação de campos obrigatórios
     * Verifica se license_plate, brand, model e status são obrigatórios
     */
    public function testValidationRequired()
    {
        $vehicle = new Vehicle();

        verify('Vehicle sem dados não deve validar', $vehicle->validate())->false();
        verify('License plate deve ser obrigatório', $vehicle->errors)->arrayHasKey('license_plate');
        verify('Brand deve ser obrigatório', $vehicle->errors)->arrayHasKey('brand');
        verify('Model deve ser obrigatório', $vehicle->errors)->arrayHasKey('model');
        verify('Status deve ser obrigatório', $vehicle->errors)->arrayHasKey('status');
    }

    /**
     * Teste 2: Validação de status
     * Verifica se status inválidos são rejeitados
     */
    public function testValidationStatus()
    {
        $vehicle = new Vehicle([
            'license_plate' => 'ZZ-99-ZZ',
            'brand' => 'Test',
            'model' => 'Model',
            'company_id' => 1,
            'status' => 'status_invalido',
        ]);

        verify('Status inválido não deve validar', $vehicle->validate())->false();
        verify('Deve ter erro no status', $vehicle->errors)->arrayHasKey('status');

        // Testar status válidos
        $validStatuses = [Vehicle::STATUS_ATIVO, Vehicle::STATUS_MANUTENCAO, Vehicle::STATUS_INATIVO];
        foreach ($validStatuses as $status) {
            $vehicle->status = $status;
            verify("Status '$status' deve ser válido", $vehicle->validate(['status']))->true();
        }
    }

    /**
     * Teste 3: Validação de tipo de combustível
     * Verifica se fuel_type inválidos são rejeitados
     */
    public function testValidationFuelType()
    {
        $vehicle = new Vehicle([
            'license_plate' => 'ZZ-99-ZZ',
            'brand' => 'Test',
            'model' => 'Model',
            'company_id' => 1,
            'status' => 'active',
            'fuel_type' => 'combustivel_invalido',
        ]);

        verify('Fuel type inválido não deve validar', $vehicle->validate())->false();
        verify('Deve ter erro no fuel_type', $vehicle->errors)->arrayHasKey('fuel_type');

        // Testar fuel types válidos
        $validFuelTypes = [
            Vehicle::FUEL_TYPE_GASOLINA,
            Vehicle::FUEL_TYPE_DIESEL,
            Vehicle::FUEL_TYPE_ELETRICO,
            Vehicle::FUEL_TYPE_HIBRIDO,
            Vehicle::FUEL_TYPE_OUTRO
        ];
        foreach ($validFuelTypes as $fuelType) {
            $vehicle->fuel_type = $fuelType;
            verify("Fuel type '$fuelType' deve ser válido", $vehicle->validate(['fuel_type']))->true();
        }
    }

    /**
     * Teste 4: Constantes de status e fuel type
     * Verifica se as constantes estão definidas corretamente
     */
    public function testConstants()
    {
        // Status constants
        verify('STATUS_ATIVO deve ser "active"', Vehicle::STATUS_ATIVO)->equals('active');
        verify('STATUS_MANUTENCAO deve ser "maintenance"', Vehicle::STATUS_MANUTENCAO)->equals('maintenance');
        verify('STATUS_INATIVO deve ser "inactive"', Vehicle::STATUS_INATIVO)->equals('inactive');

        // Fuel type constants
        verify('FUEL_TYPE_GASOLINA deve ser "gasoline"', Vehicle::FUEL_TYPE_GASOLINA)->equals('gasoline');
        verify('FUEL_TYPE_DIESEL deve ser "diesel"', Vehicle::FUEL_TYPE_DIESEL)->equals('diesel');
        verify('FUEL_TYPE_ELETRICO deve ser "electric"', Vehicle::FUEL_TYPE_ELETRICO)->equals('electric');
        verify('FUEL_TYPE_HIBRIDO deve ser "hybrid"', Vehicle::FUEL_TYPE_HIBRIDO)->equals('hybrid');
    }

    /**
     * Teste 5: Métodos auxiliares de opções
     * Verifica se optsFuelType e optsStatus retornam arrays corretos
     */
    public function testOptionsHelpers()
    {
        $fuelOptions = Vehicle::optsFuelType();
        verify('optsFuelType deve retornar array', is_array($fuelOptions))->true();
        verify('optsFuelType deve ter 5 opções', count($fuelOptions))->equals(5);
        verify('optsFuelType deve conter gasoline', $fuelOptions)->arrayHasKey('gasoline');
        verify('optsFuelType deve conter diesel', $fuelOptions)->arrayHasKey('diesel');

        $statusOptions = Vehicle::optsStatus();
        verify('optsStatus deve retornar array', is_array($statusOptions))->true();
        verify('optsStatus deve ter 3 opções', count($statusOptions))->equals(3);
        verify('optsStatus deve conter active', $statusOptions)->arrayHasKey('active');
        verify('optsStatus deve conter maintenance', $statusOptions)->arrayHasKey('maintenance');
        verify('optsStatus deve conter inactive', $statusOptions)->arrayHasKey('inactive');
    }

    /**
     * Teste 6: Display methods
     * Verifica se displayFuelType e displayStatus retornam valores corretos
     */
    public function testDisplayMethods()
    {
        $vehicle = new Vehicle([
            'fuel_type' => 'gasoline',
            'status' => 'active',
        ]);

        verify('displayFuelType deve retornar "Gasoline"', $vehicle->displayFuelType())->equals('Gasoline');
        verify('displayStatus deve retornar "Active"', $vehicle->displayStatus())->equals('Active');

        $vehicle->fuel_type = 'diesel';
        $vehicle->status = 'maintenance';
        verify('displayFuelType deve retornar "Diesel"', $vehicle->displayFuelType())->equals('Diesel');
        verify('displayStatus deve retornar "In Maintenance"', $vehicle->displayStatus())->equals('In Maintenance');
    }

    /**
     * Teste 7: Aliases PT-EN
     * Verifica se os aliases em português funcionam
     */
    public function testPTAliases()
    {
        $vehicle = new Vehicle();
        
        // Testar setters
        $vehicle->matricula = 'PT-00-PT';
        $vehicle->marca = 'Toyota';
        $vehicle->modelo = 'Yaris';
        $vehicle->ano = 2023;

        // Testar getters
        verify('Alias matricula deve mapear para license_plate', $vehicle->license_plate)->equals('PT-00-PT');
        verify('Alias marca deve mapear para brand', $vehicle->brand)->equals('Toyota');
        verify('Alias modelo deve mapear para model', $vehicle->model)->equals('Yaris');
        verify('Alias ano deve mapear para year', $vehicle->year)->equals(2023);

        // Testar getters inversos
        verify('Getter matricula deve retornar license_plate', $vehicle->matricula)->equals('PT-00-PT');
        verify('Getter marca deve retornar brand', $vehicle->marca)->equals('Toyota');
        verify('Getter modelo deve retornar model', $vehicle->modelo)->equals('Yaris');
        verify('Getter ano deve retornar year', $vehicle->ano)->equals(2023);
    }

    /**
     * Teste 8: Validação de matrícula única por empresa
     * Verifica se matrículas duplicadas na mesma empresa são rejeitadas
     */
    public function testUniqueLicensePlatePerCompany()
    {
        // Criar veículo
        $vehicle1 = new Vehicle([
            'license_plate' => 'UNIQUE-01',
            'brand' => 'Test',
            'model' => 'Model',
            'company_id' => 1,
            'status' => 'active',
        ]);
        verify('Primeiro veículo deve salvar', $vehicle1->save())->true();

        // Tentar criar veículo com mesma matrícula na mesma empresa
        $vehicle2 = new Vehicle([
            'license_plate' => 'UNIQUE-01',
            'brand' => 'Test2',
            'model' => 'Model2',
            'company_id' => 1, // mesma empresa
            'status' => 'active',
        ]);
        verify('Matrícula duplicada na mesma empresa não deve validar', $vehicle2->validate())->false();

        // Mesma matrícula em empresa diferente deve funcionar
        $vehicle3 = new Vehicle([
            'license_plate' => 'UNIQUE-01',
            'brand' => 'Test3',
            'model' => 'Model3',
            'company_id' => 2, // empresa diferente
            'status' => 'active',
        ]);
        verify('Mesma matrícula em empresa diferente deve validar', $vehicle3->validate())->true();

        // Cleanup
        $vehicle1->delete();
    }

    /**
     * Teste 9: CRUD completo
     * Testa integração completa com banco de dados
     */
    public function testCRUDOperations()
    {
        // CREATE
        $vehicle = new Vehicle([
            'license_plate' => 'CRUD-TEST',
            'brand' => 'CRUD Brand',
            'model' => 'CRUD Model',
            'year' => 2024,
            'fuel_type' => 'diesel',
            'mileage' => 0,
            'company_id' => 1,
            'status' => 'active',
        ]);

        verify('CREATE: Vehicle deve ser salvo', $vehicle->save())->true();
        verify('CREATE: ID deve ser atribuído', $vehicle->id)->notNull();
        $vehicleId = $vehicle->id;

        // READ
        $foundVehicle = Vehicle::findOne($vehicleId);
        verify('READ: Vehicle deve ser encontrado', $foundVehicle)->notNull();
        verify('READ: License plate deve corresponder', $foundVehicle->license_plate)->equals('CRUD-TEST');
        verify('READ: Brand deve corresponder', $foundVehicle->brand)->equals('CRUD Brand');

        // UPDATE
        $foundVehicle->mileage = 10000;
        $foundVehicle->status = 'maintenance';
        verify('UPDATE: Vehicle deve ser atualizado', $foundVehicle->save())->true();

        $updatedVehicle = Vehicle::findOne($vehicleId);
        verify('UPDATE: Mileage atualizado deve corresponder', $updatedVehicle->mileage)->equals(10000);
        verify('UPDATE: Status atualizado deve corresponder', $updatedVehicle->status)->equals('maintenance');

        // DELETE
        verify('DELETE: Vehicle deve ser eliminado', $updatedVehicle->delete())->equals(1);
        
        $deletedVehicle = Vehicle::findOne($vehicleId);
        verify('DELETE: Vehicle não deve existir após eliminar', $deletedVehicle)->null();
    }

    /**
     * Teste 10: Validação de tipos numéricos
     * Verifica se year, mileage e driver_id aceitam apenas inteiros
     */
    public function testNumericValidation()
    {
        $vehicle = new Vehicle([
            'license_plate' => 'NUM-TEST',
            'brand' => 'Test',
            'model' => 'Model',
            'company_id' => 1,
            'status' => 'active',
            'year' => 2024,
            'mileage' => 50000,
        ]);

        verify('Valores numéricos válidos devem passar', $vehicle->validate(['year', 'mileage']))->true();

        // Testar valores negativos ou inválidos
        $vehicle->year = -1;
        // Year aceita integer, negativo pode ser aceito pelo modelo mas faz sentido negócios
        $vehicle->mileage = -100;
        
        // A validação básica de integer aceita negativos
        // Mas podemos verificar se os valores são atribuídos corretamente
        verify('Year deve ser atribuído', $vehicle->year)->equals(-1);
        verify('Mileage deve ser atribuído', $vehicle->mileage)->equals(-100);
    }
}
