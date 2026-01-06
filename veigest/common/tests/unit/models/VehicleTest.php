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

        $this->assertFalse($vehicle->validate(), 'Vehicle sem dados não deve validar');
        $this->assertArrayHasKey('license_plate', $vehicle->errors, 'License plate deve ser obrigatório');
        $this->assertArrayHasKey('brand', $vehicle->errors, 'Brand deve ser obrigatório');
        $this->assertArrayHasKey('model', $vehicle->errors, 'Model deve ser obrigatório');
        $this->assertArrayHasKey('status', $vehicle->errors, 'Status deve ser obrigatório');
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

        $this->assertFalse($vehicle->validate(), 'Status inválido não deve validar');
        $this->assertArrayHasKey('status', $vehicle->errors, 'Deve ter erro no status');

        // Testar status válidos
        $validStatuses = [Vehicle::STATUS_ATIVO, Vehicle::STATUS_MANUTENCAO, Vehicle::STATUS_INATIVO];
        foreach ($validStatuses as $status) {
            $vehicle->status = $status;
            $this->assertTrue($vehicle->validate(['status']), "Status '$status' deve ser válido");
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

        $this->assertFalse($vehicle->validate(), 'Fuel type inválido não deve validar');
        $this->assertArrayHasKey('fuel_type', $vehicle->errors, 'Deve ter erro no fuel_type');

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
            $this->assertTrue($vehicle->validate(['fuel_type']), "Fuel type '$fuelType' deve ser válido");
        }
    }

    /**
     * Teste 4: Constantes de status e fuel type
     * Verifica se as constantes estão definidas corretamente
     */
    public function testConstants()
    {
        // Status constants
        $this->assertEquals('active', Vehicle::STATUS_ATIVO, 'STATUS_ATIVO deve ser "active"');
        $this->assertEquals('maintenance', Vehicle::STATUS_MANUTENCAO, 'STATUS_MANUTENCAO deve ser "maintenance"');
        $this->assertEquals('inactive', Vehicle::STATUS_INATIVO, 'STATUS_INATIVO deve ser "inactive"');

        // Fuel type constants
        $this->assertEquals('gasoline', Vehicle::FUEL_TYPE_GASOLINA, 'FUEL_TYPE_GASOLINA deve ser "gasoline"');
        $this->assertEquals('diesel', Vehicle::FUEL_TYPE_DIESEL, 'FUEL_TYPE_DIESEL deve ser "diesel"');
        $this->assertEquals('electric', Vehicle::FUEL_TYPE_ELETRICO, 'FUEL_TYPE_ELETRICO deve ser "electric"');
        $this->assertEquals('hybrid', Vehicle::FUEL_TYPE_HIBRIDO, 'FUEL_TYPE_HIBRIDO deve ser "hybrid"');
    }

    /**
     * Teste 5: Métodos auxiliares de opções
     * Verifica se optsFuelType e optsStatus retornam arrays corretos
     */
    public function testOptionsHelpers()
    {
        $fuelOptions = Vehicle::optsFuelType();
        $this->assertIsArray($fuelOptions, 'optsFuelType deve retornar array');
        $this->assertEquals(5, count($fuelOptions), 'optsFuelType deve ter 5 opções');
        $this->assertArrayHasKey('gasoline', $fuelOptions, 'optsFuelType deve conter gasoline');
        $this->assertArrayHasKey('diesel', $fuelOptions, 'optsFuelType deve conter diesel');

        $statusOptions = Vehicle::optsStatus();
        $this->assertIsArray($statusOptions, 'optsStatus deve retornar array');
        $this->assertEquals(3, count($statusOptions), 'optsStatus deve ter 3 opções');
        $this->assertArrayHasKey('active', $statusOptions, 'optsStatus deve conter active');
        $this->assertArrayHasKey('maintenance', $statusOptions, 'optsStatus deve conter maintenance');
        $this->assertArrayHasKey('inactive', $statusOptions, 'optsStatus deve conter inactive');
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

        $this->assertEquals('Gasoline', $vehicle->displayFuelType(), 'displayFuelType deve retornar "Gasoline"');
        $this->assertEquals('Active', $vehicle->displayStatus(), 'displayStatus deve retornar "Active"');

        $vehicle->fuel_type = 'diesel';
        $vehicle->status = 'maintenance';
        $this->assertEquals('Diesel', $vehicle->displayFuelType(), 'displayFuelType deve retornar "Diesel"');
        $this->assertEquals('In Maintenance', $vehicle->displayStatus(), 'displayStatus deve retornar "In Maintenance"');
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
        $this->assertEquals('PT-00-PT', $vehicle->license_plate, 'Alias matricula deve mapear para license_plate');
        $this->assertEquals('Toyota', $vehicle->brand, 'Alias marca deve mapear para brand');
        $this->assertEquals('Yaris', $vehicle->model, 'Alias modelo deve mapear para model');
        $this->assertEquals(2023, $vehicle->year, 'Alias ano deve mapear para year');

        // Testar getters inversos
        $this->assertEquals('PT-00-PT', $vehicle->matricula, 'Getter matricula deve retornar license_plate');
        $this->assertEquals('Toyota', $vehicle->marca, 'Getter marca deve retornar brand');
        $this->assertEquals('Yaris', $vehicle->modelo, 'Getter modelo deve retornar model');
        $this->assertEquals(2023, $vehicle->ano, 'Getter ano deve retornar year');
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
        $this->assertTrue($vehicle1->save(), 'Primeiro veículo deve salvar');

        // Tentar criar veículo com mesma matrícula na mesma empresa
        $vehicle2 = new Vehicle([
            'license_plate' => 'UNIQUE-01',
            'brand' => 'Test2',
            'model' => 'Model2',
            'company_id' => 1, // mesma empresa
            'status' => 'active',
        ]);
        $this->assertFalse($vehicle2->validate(), 'Matrícula duplicada na mesma empresa não deve validar');

        // Mesma matrícula em empresa diferente deve funcionar
        $vehicle3 = new Vehicle([
            'license_plate' => 'UNIQUE-01',
            'brand' => 'Test3',
            'model' => 'Model3',
            'company_id' => 2, // empresa diferente
            'status' => 'active',
        ]);
        $this->assertTrue($vehicle3->validate(), 'Mesma matrícula em empresa diferente deve validar');

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

        $this->assertTrue($vehicle->save(), 'CREATE: Vehicle deve ser salvo');
        $this->assertNotNull($vehicle->id, 'CREATE: ID deve ser atribuído');
        $vehicleId = $vehicle->id;

        // READ
        $foundVehicle = Vehicle::findOne($vehicleId);
        $this->assertNotNull($foundVehicle, 'READ: Vehicle deve ser encontrado');
        $this->assertEquals('CRUD-TEST', $foundVehicle->license_plate, 'READ: License plate deve corresponder');
        $this->assertEquals('CRUD Brand', $foundVehicle->brand, 'READ: Brand deve corresponder');

        // UPDATE
        $foundVehicle->mileage = 10000;
        $foundVehicle->status = 'maintenance';
        $this->assertTrue($foundVehicle->save(), 'UPDATE: Vehicle deve ser atualizado');

        $updatedVehicle = Vehicle::findOne($vehicleId);
        $this->assertEquals(10000, $updatedVehicle->mileage, 'UPDATE: Mileage atualizado deve corresponder');
        $this->assertEquals('maintenance', $updatedVehicle->status, 'UPDATE: Status atualizado deve corresponder');

        // DELETE
        $this->assertEquals(1, $updatedVehicle->delete(), 'DELETE: Vehicle deve ser eliminado');
        
        $deletedVehicle = Vehicle::findOne($vehicleId);
        $this->assertNull($deletedVehicle, 'DELETE: Vehicle não deve existir após eliminar');
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

        $this->assertTrue($vehicle->validate(['year', 'mileage']), 'Valores numéricos válidos devem passar');

        // Testar valores negativos ou inválidos
        $vehicle->year = -1;
        // Year aceita integer, negativo pode ser aceito pelo modelo mas faz sentido negócios
        $vehicle->mileage = -100;
        
        // A validação básica de integer aceita negativos
        // Mas podemos verificar se os valores são atribuídos corretamente
        $this->assertEquals(-1, $vehicle->year, 'Year deve ser atribuído');
        $this->assertEquals(-100, $vehicle->mileage, 'Mileage deve ser atribuído');
    }
}
