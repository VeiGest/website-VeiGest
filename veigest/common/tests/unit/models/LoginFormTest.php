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

        verify('Login deve retornar false', $model->login())->false();
        verify('Utilizador deve permanecer como guest', Yii::$app->user->isGuest)->true();
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

        verify('Login deve retornar false', $model->login())->false();
        verify('Deve ter erro no campo password', $model->errors)->arrayHasKey('password');
        verify('Utilizador deve permanecer como guest', Yii::$app->user->isGuest)->true();
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

        verify('Validação deve falhar', $model->validate())->false();
        verify('Deve ter erro no username', $model->errors)->arrayHasKey('username');
        verify('Deve ter erro no password', $model->errors)->arrayHasKey('password');
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

        verify('Login de utilizador inativo deve retornar false', $model->login())->false();
        verify('Utilizador deve permanecer como guest', Yii::$app->user->isGuest)->true();
    }

    /**
     * Teste 5: Opção Remember Me
     */
    public function testRememberMeValidation()
    {
        $model = new LoginForm([
            'username' => 'test_admin',
            'password' => 'test',
            'rememberMe' => 'invalid_value',
        ]);

        verify('RememberMe com valor inválido deve falhar validação', $model->validate())->false();
        
        $model2 = new LoginForm([
            'username' => 'test_admin',
            'password' => 'test',
            'rememberMe' => true,
        ]);
        
        // RememberMe é boolean, não deve causar erro de validação
        $model2->validate();
        verify('RememberMe não deve ter erro', $model2->errors)->arrayHasNotKey('rememberMe');
    }
}
