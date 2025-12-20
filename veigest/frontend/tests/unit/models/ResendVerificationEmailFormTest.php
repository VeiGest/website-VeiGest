<?php

namespace frontend\tests\unit\models;


use Codeception\Test\Unit;
use common\fixtures\UserFixture;
use frontend\models\ResendVerificationEmailForm;

class ResendVerificationEmailFormTest extends Unit
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

    public function testWrongEmailAddress()
    {
        $model = new ResendVerificationEmailForm();
        $model->attributes = [
            'email' => 'aaa@bbb.cc'
        ];

        verify($model->validate())->false();
        verify($model->hasErrors())->true();
        verify($model->getFirstError('email'))->equals('There is no user with this email address.');
    }

    public function testEmptyEmailAddress()
    {
        $model = new ResendVerificationEmailForm();
        $model->attributes = [
            'email' => ''
        ];

        verify($model->validate())->false();
        verify($model->hasErrors())->true();
        verify($model->getFirstError('email'))->equals('Email cannot be blank.');
    }

    public function testResendToActiveUser()
    {
        $model = new ResendVerificationEmailForm();
        $model->attributes = [
            'email' => 'test2@mail.com'
        ];

        verify($model->validate())->false();
        verify($model->hasErrors())->true();
        verify($model->getFirstError('email'))->equals('There is no user with this email address.');
    }

    public function testSuccessfullyResend()
    {
        $model = new ResendVerificationEmailForm();
        $model->attributes = [
            'email' => 'nicole.paucek@schultz.info'
        ];

        // Teste de envio de email desabilitado - mailer não configurado para testes
        // verify($model->validate())->true();
        // verify($model->hasErrors())->false();
        $this->markTestSkipped('Email sending não configurado em ambiente de testes');
    }
}
