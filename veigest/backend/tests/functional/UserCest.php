<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\models\User;
use common\fixtures\UserFixture;
use common\fixtures\CompanyFixture;

/**
 * Testes Funcionais - Gestão de Utilizadores
 * 
 * Testa as funcionalidades de listagem, criação e visualização de utilizadores.
 * 
 * Nota: Estes testes são simplificados porque o acesso ao backend requer 
 * roles RBAC configuradas, que não estão disponíveis nos fixtures.
 * 
 * @group functional
 * @group backend
 * @group users
 */
class UserCest
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
     * Executado antes de cada teste
     */
    public function _before(FunctionalTester $I)
    {
        // Não faz login para testar o controle de acesso
    }

    /**
     * Teste 1: Utilizadores não autenticados são redirecionados para login
     * Verifica se o controle de acesso funciona
     */
    public function testUserIndexRequiresAuthentication(FunctionalTester $I)
    {
        $I->wantTo('verificar que lista de utilizadores requer autenticação');
        
        $I->amOnPage('/user/index');
        // Deve redirecionar para login
        $I->seeInCurrentUrl('login');
    }

    /**
     * Teste 2: Página de criação requer autenticação
     */
    public function testUserCreateRequiresAuthentication(FunctionalTester $I)
    {
        $I->wantTo('verificar que criação de utilizador requer autenticação');
        
        $I->amOnPage('/user/create');
        // Deve redirecionar para login
        $I->seeInCurrentUrl('login');
    }

    /**
     * Teste 3: Visualização requer autenticação
     */
    public function testUserViewRequiresAuthentication(FunctionalTester $I)
    {
        $I->wantTo('verificar que visualização de utilizador requer autenticação');
        
        $I->amOnPage('/user/view?id=100');
        // Deve redirecionar para login
        $I->seeInCurrentUrl('login');
    }
}
