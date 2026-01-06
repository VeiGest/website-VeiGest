<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use frontend\models\Maintenance;
use common\fixtures\UserFixture;
use common\fixtures\CompanyFixture;
use common\fixtures\VehicleFixture;
use common\fixtures\MaintenanceFixture;

/**
 * Testes Funcionais - Gestão de Manutenções
 * 
 * Testa as funcionalidades de listagem, criação e gestão de manutenções.
 * 
 * @group functional
 * @group frontend
 * @group maintenance
 */
class MaintenanceCest
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
            'maintenance' => [
                'class' => MaintenanceFixture::class,
                'dataFile' => '@common/tests/_data/maintenance.php'
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
     * Teste 1: Lista de manutenções é acessível
     */
    public function testMaintenanceIndexIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se a lista de manutenções está acessível');
        
        $I->amOnPage('/maintenance/index');
        $I->seeResponseCodeIs(200);
        $I->see('Manuten');
    }

    /**
     * Teste 2: Lista de manutenções mostra dados
     */
    public function testMaintenanceListShowsData(FunctionalTester $I)
    {
        $I->wantTo('verificar se a lista mostra manutenções');
        
        $I->amOnPage('/maintenance/index');
        $I->seeElement('table');
    }

    /**
     * Teste 3: Formulário de criação é acessível
     */
    public function testMaintenanceCreateFormIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se formulário de criação está acessível');
        
        $I->amOnPage('/maintenance/create');
        $I->seeResponseCodeIs(200);
        $I->seeElement('form');
    }

    /**
     * Teste 4: Validação de criação com dados vazios
     */
    public function testMaintenanceCreateValidation(FunctionalTester $I)
    {
        $I->wantTo('verificar validação do formulário de criação');
        
        $I->amOnPage('/maintenance/create');
        $I->click('button[type="submit"]');
        
        $I->see('cannot be blank');
    }

    /**
     * Teste 5: Visualização de manutenção
     */
    public function testMaintenanceView(FunctionalTester $I)
    {
        $I->wantTo('visualizar detalhes de uma manutenção');
        
        $I->amOnPage('/maintenance/view?id=1');
        $I->seeResponseCodeIs(200);
        $I->see('Óleo');
    }
}
