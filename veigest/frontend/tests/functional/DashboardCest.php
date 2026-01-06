<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use common\fixtures\UserFixture;
use common\fixtures\CompanyFixture;
use common\fixtures\VehicleFixture;

/**
 * Testes Funcionais - Dashboard
 * 
 * Testa as funcionalidades do dashboard principal.
 * 
 * @group functional
 * @group frontend
 * @group dashboard
 */
class DashboardCest
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
     * Teste 1: Dashboard é acessível após login
     */
    public function testDashboardIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se o dashboard está acessível');
        
        $I->amOnPage('/dashboard/index');
        $I->seeResponseCodeIs(200);
        $I->see('Dashboard');
    }

    /**
     * Teste 2: Dashboard mostra estatísticas
     */
    public function testDashboardShowsStatistics(FunctionalTester $I)
    {
        $I->wantTo('verificar se o dashboard mostra estatísticas');
        
        $I->amOnPage('/dashboard/index');
        $I->seeElement('.card');
    }

    /**
     * Teste 3: Menu de navegação funciona
     */
    public function testNavigationMenuExists(FunctionalTester $I)
    {
        $I->wantTo('verificar se o menu de navegação existe');
        
        $I->amOnPage('/dashboard/index');
        $I->seeElement('nav');
    }
}
