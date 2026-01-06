<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\models\User;
use common\fixtures\UserFixture;
use common\fixtures\CompanyFixture;

/**
 * Testes Funcionais - Login Backend (Back-office)
 * 
 * Testa o processo de autenticação no painel administrativo.
 * Conforme requisito: "Um dos testes funcionais deverá ser o de login no back-office"
 * 
 * @group functional
 * @group backend
 * @group login
 */
class LoginCest
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
        $I->amOnPage('/site/login');
    }

    /**
     * Teste 1: Página de login é acessível
     * Verifica se a página de login carrega corretamente
     */
    public function testLoginPageIsAccessible(FunctionalTester $I)
    {
        $I->wantTo('verificar se a página de login está acessível');
        
        $I->see('Login', 'h1');
        $I->seeElement('form');
        $I->seeElement('input[name="LoginForm[username]"]');
        $I->seeElement('input[name="LoginForm[password]"]');
    }

    /**
     * Teste 2: Login com credenciais inválidas
     * Verifica se credenciais erradas são rejeitadas
     */
    public function testLoginWithInvalidCredentials(FunctionalTester $I)
    {
        $I->wantTo('verificar que credenciais inválidas são rejeitadas');
        
        $I->fillField('LoginForm[username]', 'usuario_invalido');
        $I->fillField('LoginForm[password]', 'senha_errada');
        $I->click('login-button');

        $I->seeInCurrentUrl('/site/login');
        $I->see('Incorrect username or password');
    }

    /**
     * Teste 3: Login com campos vazios
     * Verifica se a validação de campos obrigatórios funciona
     */
    public function testLoginWithEmptyFields(FunctionalTester $I)
    {
        $I->wantTo('verificar validação de campos obrigatórios');
        
        $I->fillField('LoginForm[username]', '');
        $I->fillField('LoginForm[password]', '');
        $I->click('login-button');

        $I->seeInCurrentUrl('/site/login');
        $I->see('cannot be blank');
    }

    /**
     * Teste 4: Login de Admin com sucesso
     * Verifica se admin consegue autenticar e aceder ao dashboard
     */
    public function testAdminLoginSuccessfully(FunctionalTester $I)
    {
        $I->wantTo('autenticar como admin e aceder ao dashboard');
        
        $I->fillField('LoginForm[username]', 'admin');
        $I->fillField('LoginForm[password]', 'admin');
        $I->click('login-button');

        // Admin deve ter acesso ao dashboard do backend
        $I->dontSeeInCurrentUrl('/site/login');
        $I->see('Dashboard');
    }

    /**
     * Teste 5: Login de Manager com sucesso
     * Verifica se manager consegue autenticar
     */
    public function testManagerLoginSuccessfully(FunctionalTester $I)
    {
        $I->wantTo('autenticar como manager');
        
        $I->fillField('LoginForm[username]', 'manager');
        $I->fillField('LoginForm[password]', 'manager123');
        $I->click('login-button');

        // Manager pode ter acesso limitado ao backend
        $I->dontSeeInCurrentUrl('/site/login');
    }

    /**
     * Teste 6: Driver não pode aceder ao backend
     * Verifica se driver é bloqueado (acesso proibido)
     */
    public function testDriverCannotAccessBackend(FunctionalTester $I)
    {
        $I->wantTo('verificar que driver não pode aceder ao backend');
        
        $I->fillField('LoginForm[username]', 'driver1');
        $I->fillField('LoginForm[password]', 'driver123');
        $I->click('login-button');

        // Driver deve ser redirecionado ou receber erro de acesso
        // O backend bloqueia drivers com ForbiddenHttpException
        $I->seeInCurrentUrl('login');
    }

    /**
     * Teste 7: Logout funciona corretamente
     * Verifica se o utilizador pode fazer logout
     */
    public function testLogout(FunctionalTester $I)
    {
        $I->wantTo('verificar que logout funciona');
        
        // Login primeiro
        $I->fillField('LoginForm[username]', 'admin');
        $I->fillField('LoginForm[password]', 'admin');
        $I->click('login-button');
        
        // Verificar que está logado
        $I->dontSeeInCurrentUrl('/site/login');
        
        // Fazer logout
        $I->amOnPage('/site/logout');
        
        // Deve voltar para login
        $I->seeInCurrentUrl('/site/login');
    }

    /**
     * Teste 8: Remember Me checkbox existe
     * Verifica se a opção "Lembrar-me" está disponível
     */
    public function testRememberMeCheckboxExists(FunctionalTester $I)
    {
        $I->wantTo('verificar que checkbox Remember Me existe');
        
        $I->seeElement('input[name="LoginForm[rememberMe]"]');
        $I->seeElement('input[type="checkbox"]');
    }
}
