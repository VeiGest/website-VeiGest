<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use common\fixtures\UserFixture;
use common\fixtures\CompanyFixture;

/**
 * Testes Funcionais - Páginas de Serviços
 * 
 * Testa as páginas públicas de informação do frontend:
 * - Página de serviços
 * - Página de benefícios
 * - Página de preços
 * 
 * Nota: O controle de acesso a veículos usa RBAC com roles específicas
 * (manager, driver) que requerem configuração de authManager.
 * Estes testes verificam páginas públicas disponíveis para visitantes.
 * 
 * @group functional
 * @group frontend
 * @group services
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
        ];
    }

    /**
     * Teste 1: Página de serviços está acessível
     */
    public function testServicesPageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar que página de serviços está acessível');
        
        $I->amOnPage('/site/services');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 2: Página de benefícios está acessível
     */
    public function testBenefitsPageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar que página de benefícios está acessível');
        
        $I->amOnPage('/site/benefits');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 3: Página de preços está acessível
     */
    public function testPricingPageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar que página de preços está acessível');
        
        $I->amOnPage('/site/pricing');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 4: Página de contactos está acessível
     */
    public function testContactPageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar que página de contactos está acessível');
        
        $I->amOnPage('/site/contact');
        $I->seeResponseCodeIs(200);
    }
}
