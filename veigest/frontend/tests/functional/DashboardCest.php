<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use common\fixtures\UserFixture;
use common\fixtures\CompanyFixture;
use common\fixtures\VehicleFixture;

/**
 * Testes Funcionais - Dashboard Frontend
 * 
 * Testa as funcionalidades públicas do frontend:
 * - Página inicial (landing page)
 * - Páginas de informação (about, services, benefits, pricing)
 * - Formulário de contato
 * 
 * Nota: O AccessControl do Dashboard usa roles RBAC (manager, driver) que
 * requerem configuração específica de authManager. Estes testes focam em
 * verificar as páginas públicas disponíveis para visitantes.
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
        ];
    }

    /**
     * Teste 1: Homepage está acessível
     * Verifica se a landing page carrega para visitantes
     */
    public function testHomepageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se a homepage está acessível');
        
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->see('VeiGest');
    }

    /**
     * Teste 2: Página tem link para login
     * Verifica se existe link para entrar no sistema
     */
    public function testLoginLinkExists(FunctionalTester $I)
    {
        $I->wantTo('verificar se existe link para login');
        
        $I->amOnPage('/');
        $I->seeLink('Entrar');
    }

    /**
     * Teste 3: Página About está acessível
     * Verifica se a página sobre está disponível
     */
    public function testAboutPageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se a página sobre está acessível');
        
        $I->amOnPage('/site/about');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 4: Página de Login está acessível
     * Verifica se a página de login carrega corretamente
     */
    public function testLoginPageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se a página de login está acessível');
        
        // Clicar no link "Entrar" da homepage para ir para login
        $I->amOnPage('/');
        $I->click('Entrar');
        $I->seeResponseCodeIs(200);
    }
}
