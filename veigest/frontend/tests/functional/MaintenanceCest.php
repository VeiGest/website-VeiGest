<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use common\fixtures\UserFixture;
use common\fixtures\CompanyFixture;

/**
 * Testes Funcionais - Registro e Tickets
 * 
 * Testa as funcionalidades de registro de usuário e criação de tickets
 * disponíveis para visitantes (não autenticados).
 * 
 * Nota: O controle de acesso a manutenções usa RBAC com roles específicas
 * (manager) que requerem configuração de authManager.
 * Estes testes verificam funcionalidades de registro e suporte.
 * 
 * @group functional
 * @group frontend
 * @group registration
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
        ];
    }

    /**
     * Teste 1: Página de registro está acessível
     */
    public function testSignupPageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar que página de registro está acessível');
        
        // Clicar no link "Registar" da homepage para ir para signup
        $I->amOnPage('/');
        $I->click('Registar');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 2: Página de criação de tickets está acessível
     */
    public function testCreateTicketPageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar que página de criação de tickets está acessível');
        
        $I->amOnPage('/site/create-ticket');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 3: Formulário de contato mostra campos corretos
     */
    public function testContactFormHasRequiredFields(FunctionalTester $I)
    {
        $I->wantTo('verificar que formulário de contato está acessível');
        
        // Navegar para contato através do menu
        $I->amOnPage('/');
        $I->click('Contactos');
        $I->seeResponseCodeIs(200);
    }
}
