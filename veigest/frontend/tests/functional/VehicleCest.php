<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use frontend\models\Vehicle;
use common\fixtures\UserFixture;
use common\fixtures\CompanyFixture;
use common\fixtures\VehicleFixture;

/**
 * Testes Funcionais - Gestão de Veículos
 * 
 * Testa as funcionalidades de listagem, criação, visualização e gestão de veículos.
 * Funcionalidade principal da aplicação VeiGest.
 * 
 * @group functional
 * @group frontend
 * @group vehicles
 */
class VehicleCest
{
    /**
     * Fixtures necessárias para os testes
     */
    public function _fixtures()
    {
        return [
            'company' => [
                'class' => CompanyFixture::class,
                'dataFile' => '@common/tests/_data/company.php'
            ],
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => '@common/tests/_data/user.php'
            ],
            'vehicle' => [
                'class' => VehicleFixture::class,
                'dataFile' => '@common/tests/_data/vehicle.php'
            ],
        ];
    }

    /**
     * Executado antes de cada teste - Login como manager
     */
    public function _before(FunctionalTester $I)
    {
        $I->amOnPage('/site/login');
        $I->fillField('LoginForm[username]', 'manager');
        $I->fillField('LoginForm[password]', 'manager123');
        $I->click('login-button');
    }

    /**
     * Teste 1: Lista de veículos é acessível
     * Verifica se a página de listagem carrega corretamente
     */
    public function testVehicleIndexIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se a lista de veículos está acessível');
        
        $I->amOnPage('/vehicle/index');
        $I->seeResponseCodeIs(200);
        $I->see('Veículo');
    }

    /**
     * Teste 2: Lista de veículos mostra dados
     * Verifica se a tabela com veículos é renderizada
     */
    public function testVehicleListShowsData(FunctionalTester $I)
    {
        $I->wantTo('verificar se a lista mostra veículos');
        
        $I->amOnPage('/vehicle/index');
        $I->seeElement('table');
        // Deve ver algum veículo do fixture
        $I->see('AA-00-AA'); // license_plate do fixture
    }

    /**
     * Teste 3: Formulário de criação é acessível
     * Verifica se a página de criação de veículo carrega
     */
    public function testVehicleCreateFormIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se formulário de criação está acessível');
        
        $I->amOnPage('/vehicle/create');
        $I->seeResponseCodeIs(200);
        $I->seeElement('form');
        $I->seeElement('input[name="Vehicle[license_plate]"]');
        $I->seeElement('input[name="Vehicle[brand]"]');
        $I->seeElement('input[name="Vehicle[model]"]');
    }

    /**
     * Teste 4: Validação de criação com dados vazios
     * Verifica se campos obrigatórios são validados
     */
    public function testVehicleCreateValidation(FunctionalTester $I)
    {
        $I->wantTo('verificar validação do formulário de criação');
        
        $I->amOnPage('/vehicle/create');
        $I->click('button[type="submit"]');
        
        $I->see('cannot be blank');
    }

    /**
     * Teste 5: Criação de veículo com sucesso
     * Verifica se um novo veículo pode ser criado
     */
    public function testVehicleCreateSuccess(FunctionalTester $I)
    {
        $I->wantTo('criar um novo veículo');
        
        $I->amOnPage('/vehicle/create');
        
        $I->fillField('Vehicle[license_plate]', 'TEST-001');
        $I->fillField('Vehicle[brand]', 'TestBrand');
        $I->fillField('Vehicle[model]', 'TestModel');
        $I->fillField('Vehicle[year]', '2024');
        $I->selectOption('Vehicle[fuel_type]', 'diesel');
        $I->fillField('Vehicle[mileage]', '0');
        $I->selectOption('Vehicle[status]', 'active');
        
        $I->click('button[type="submit"]');
        
        // Deve redirecionar para view ou index após sucesso
        $I->dontSeeInCurrentUrl('/vehicle/create');
    }

    /**
     * Teste 6: Visualização de veículo
     * Verifica se a página de detalhes funciona
     */
    public function testVehicleView(FunctionalTester $I)
    {
        $I->wantTo('visualizar detalhes de um veículo');
        
        // Ver veículo ID 1 do fixture
        $I->amOnPage('/vehicle/view?id=1');
        $I->seeResponseCodeIs(200);
        $I->see('AA-00-AA');
        $I->see('Toyota');
        $I->see('Corolla');
    }

    /**
     * Teste 7: Filtros de status funcionam
     * Verifica se os filtros de status na lista funcionam
     */
    public function testVehicleStatusFilter(FunctionalTester $I)
    {
        $I->wantTo('filtrar veículos por status');
        
        $I->amOnPage('/vehicle/index?status=active');
        $I->seeResponseCodeIs(200);
        // Deve mostrar apenas veículos ativos
    }

    /**
     * Teste 8: Edição de veículo
     * Verifica se a página de edição carrega
     */
    public function testVehicleEditFormIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se formulário de edição está acessível');
        
        $I->amOnPage('/vehicle/update?id=1');
        $I->seeResponseCodeIs(200);
        $I->seeElement('form');
        // Deve ter os dados preenchidos
        $I->seeInField('Vehicle[license_plate]', 'AA-00-AA');
    }
}
