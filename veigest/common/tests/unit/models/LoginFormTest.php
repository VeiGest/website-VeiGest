<?php

namespace common\tests\unit\models;

use Yii;
use common\models\LoginForm;
use common\models\User;
use common\fixtures\UserFixture;

/**
 * Testes Unitários - LoginForm
 * 
 * Testa a validação e autenticação do formulário de login.
 * 
 * @group unit
 * @group models
 * @group auth
 */
class LoginFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function _fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ];
    }

    /**
     * Teste 1: Login com utilizador inexistente deve falhar
     */
    public function testLoginNoUser()
    {
        $model = new LoginForm([
            'username' => 'utilizador_inexistente',
            'password' => 'password_qualquer',
        ]);

        $this->assertFalse($model->login(), 'Login deve retornar false');
        $this->assertTrue(Yii::$app->user->isGuest, 'Utilizador deve permanecer como guest');
    }

    /**
     * Teste 2: Login com password incorreta deve falhar
     */
    public function testLoginWrongPassword()
    {
        $model = new LoginForm([
            'username' => 'test_admin',
            'password' => 'password_errada',
        ]);

        $this->assertFalse($model->login(), 'Login deve retornar false');
        $this->assertArrayHasKey('password', $model->errors, 'Deve ter erro no campo password');
        $this->assertTrue(Yii::$app->user->isGuest, 'Utilizador deve permanecer como guest');
    }

    /**
     * Teste 3: Validação de campos obrigatórios
     */
    public function testValidationRequired()
    {
        $model = new LoginForm([
            'username' => '',
            'password' => '',
        ]);

        $this->assertFalse($model->validate(), 'Validação deve falhar');
        $this->assertArrayHasKey('username', $model->errors, 'Deve ter erro no username');
        $this->assertArrayHasKey('password', $model->errors, 'Deve ter erro no password');
    }

    /**
     * Teste 4: Login com utilizador inativo deve falhar
     */
    public function testLoginInactiveUser()
    {
        $model = new LoginForm([
            'username' => 'test_inactive',
            'password' => 'admin123', // password correta mas user inativo
        ]);

        $this->assertFalse($model->login(), 'Login de utilizador inativo deve retornar false');
        $this->assertTrue(Yii::$app->user->isGuest, 'Utilizador deve permanecer como guest');
    }

    /**
     * Teste 5: Opção Remember Me
     */
    public function testRememberMeValidation()
    {
        // RememberMe é boolean, valores inválidos são convertidos
        $model = new LoginForm([
            'username' => 'test_admin',
            'password' => 'admin123',
            'rememberMe' => true,
        ]);
        
        $model->validate();
        $this->assertArrayNotHasKey('rememberMe', $model->errors, 'RememberMe não deve ter erro');
        
        $model2 = new LoginForm([
            'username' => 'test_admin',
            'password' => 'admin123',
            'rememberMe' => false,
        ]);
        
        $model2->validate();
        $this->assertArrayNotHasKey('rememberMe', $model2->errors, 'RememberMe false não deve ter erro');
    }
}
