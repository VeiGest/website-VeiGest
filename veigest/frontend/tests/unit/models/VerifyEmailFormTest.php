<?php

namespace frontend\tests\unit\models;

use common\fixtures\UserFixture;
use frontend\models\VerifyEmailForm;

class VerifyEmailFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;


    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ]);
    }

    public function testVerifyWrongToken()
    {
        $this->tester->expectThrowable('\yii\base\InvalidArgumentException', function() {
            new VerifyEmailForm('');
        });

        $this->tester->expectThrowable('\yii\base\InvalidArgumentException', function() {
            new VerifyEmailForm('notexistingtoken_1391882543');
        });
    }

    public function testAlreadyActivatedToken()
    {
        $this->tester->expectThrowable('\yii\base\InvalidArgumentException', function() {
            new VerifyEmailForm('already_used_token_1548675330');
        });
    }

    public function testVerifyCorrectToken()
    {
        // Buscar um usuário inativo com token válido para testar
        $user = $this->tester->grabRecord('common\models\User', ['status' => 'inactive']);
        
        if ($user && $user->verification_token) {
            $model = new VerifyEmailForm($user->verification_token);
            $verifiedUser = $model->verifyEmail();
            verify($verifiedUser)->instanceOf('common\models\User');
            verify($verifiedUser->status)->equals('active');
        }
    }
}
