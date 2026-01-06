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
     * Executado antes de cada teste - Login como admin
     */
    public function _before(FunctionalTester $I)
    {
        $I->amOnPage('/site/login');
        $I->fillField('LoginForm[username]', 'admin');
        $I->fillField('LoginForm[password]', 'admin');
        $I->click('login-button');
    }

    /**
     * Teste 1: Admin pode ver lista de utilizadores
     * Verifica se a página de listagem é acessível
     */
    public function testUserIndexIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se a lista de utilizadores está acessível');
        
        $I->amOnPage('/user/index');
        $I->seeResponseCodeIs(200);
        $I->see('User', 'h1');
    }

    /**
     * Teste 2: Lista de utilizadores mostra dados
     * Verifica se a tabela de utilizadores é renderizada
     */
    public function testUserListShowsData(FunctionalTester $I)
    {
        $I->wantTo('verificar se a lista mostra utilizadores');
        
        $I->amOnPage('/user/index');
        $I->seeElement('table');
        $I->see('admin'); // user do fixture
    }

    /**
     * Teste 3: Formulário de criação é acessível
     * Verifica se a página de criação de utilizador carrega
     */
    public function testUserCreateFormIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se formulário de criação está acessível');
        
        $I->amOnPage('/user/create');
        $I->seeResponseCodeIs(200);
        $I->seeElement('form');
        $I->seeElement('input[name="User[username]"]');
        $I->seeElement('input[name="User[email]"]');
    }

    /**
     * Teste 4: Validação de criação com dados vazios
     * Verifica se campos obrigatórios são validados
     */
    public function testUserCreateValidation(FunctionalTester $I)
    {
        $I->wantTo('verificar validação do formulário de criação');
        
        $I->amOnPage('/user/create');
        $I->click('button[type="submit"]');
        
        $I->see('cannot be blank');
    }

    /**
     * Teste 5: Visualização de utilizador
     * Verifica se a página de detalhes funciona
     */
    public function testUserView(FunctionalTester $I)
    {
        $I->wantTo('visualizar detalhes de um utilizador');
        
        // Ver utilizador ID 100 (test_admin do fixture)
        $I->amOnPage('/user/view?id=100');
        $I->seeResponseCodeIs(200);
        $I->see('test_admin');
    }
}
