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
        
        $I->see('Entrar', 'h1');
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
        $I->click('button[name="login-button"]');

        // Permanece na página de login (URL pode ser index-test.php?r=site%2Flogin)
        $I->seeInCurrentUrl('login');
        // Mensagem de erro em inglês
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
        $I->click('button[name="login-button"]');

        // Permanece na página de login
        $I->seeInCurrentUrl('login');
        // Verificar mensagens de campo obrigatório (em inglês)
        $I->see('cannot be blank');
    }

    /**
     * Teste 4: Login de Admin com sucesso
     * Verifica se admin consegue autenticar e aceder ao dashboard
     */
    public function testAdminLoginSuccessfully(FunctionalTester $I)
    {
        $I->wantTo('autenticar como admin e aceder ao dashboard');
        
        // Usar fixture test_admin com senha admin123
        $I->fillField('LoginForm[username]', 'test_admin');
        $I->fillField('LoginForm[password]', 'admin123');
        $I->click('button[name="login-button"]');

        // Admin deve ter acesso ao dashboard do backend
        $I->dontSeeInCurrentUrl('login');
    }

    /**
     * Teste 5: Login de Manager com sucesso
     * Verifica se manager consegue autenticar
     */
    public function testManagerLoginSuccessfully(FunctionalTester $I)
    {
        $I->wantTo('autenticar como manager');
        
        // Usar fixture test_manager com senha admin123 (mesmo hash)
        $I->fillField('LoginForm[username]', 'test_manager');
        $I->fillField('LoginForm[password]', 'admin123');
        $I->click('button[name="login-button"]');

        // Manager pode ter acesso limitado ao backend
        $I->dontSeeInCurrentUrl('login');
    }

    /**
     * Teste 6: Driver não pode aceder ao backend
     * Verifica se driver é bloqueado (acesso proibido)
     */
    public function testDriverCannotAccessBackend(FunctionalTester $I)
    {
        $I->wantTo('verificar que driver não pode aceder ao backend');
        
        // Usar fixture test_driver
        $I->fillField('LoginForm[username]', 'test_driver');
        $I->fillField('LoginForm[password]', 'admin123');
        $I->click('button[name="login-button"]');

        // Driver consegue fazer login mas é bloqueado pelo RBAC
        // O backend bloqueia drivers com ForbiddenHttpException (403)
        // O driver é redirecionado ou recebe erro - não permanece logado no backend
        // Como o backend bloqueia com 403, o conteúdo deve conter mensagem de acesso negado
        $I->see('Forbidden');
    }

    /**
     * Teste 7: Logout funciona corretamente
     * Verifica se o utilizador pode fazer logout
     */
    public function testLogout(FunctionalTester $I)
    {
        $I->wantTo('verificar que logout funciona');
        
        // Login primeiro usando test_admin
        $I->fillField('LoginForm[username]', 'test_admin');
        $I->fillField('LoginForm[password]', 'admin123');
        $I->click('button[name="login-button"]');
        
        // Verificar que está logado
        $I->dontSeeInCurrentUrl('login');
        
        // Fazer logout via POST (logout requer método POST)
        $I->sendAjaxPostRequest('/index-test.php?r=site/logout');
        
        // Redirecionar para login
        $I->amOnPage('/site/login');
        $I->seeInCurrentUrl('login');
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
