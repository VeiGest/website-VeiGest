<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
?>

<section class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-center mb-4">
                <img class="w-8 h-8 mr-2" src="./images/veigestLogo.png" alt="logo">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">VeiGest</h1>
            </div>

            <h2 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white text-center">
                Entrar na sua conta
            </h2>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
            ]); ?>

            <?= $form->field($model, 'nome')->textInput([
                'autofocus' => true,
                'class' => 'bg-gray-50 border border-gray-300 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white'
            ])->label('Nome de Utilizador', ['class' => 'text-sm font-medium text-gray-900 dark:text-white']) ?>

            <?= $form->field($model, 'password')->passwordInput([
                'class' => 'bg-gray-50 border border-gray-300 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white'
            ])->label('Palavra-passe', ['class' => 'text-sm font-medium text-gray-900 dark:text-white']) ?>

            <div class="flex items-center justify-between">
                <?= $form->field($model, 'rememberMe')->checkbox()->label('Lembrar-me') ?>
                <?= Html::a('Esqueceu a senha?', ['site/request-password-reset'], ['class' => 'text-sm font-medium text-primary-600 hover:underline dark:text-primary-500']) ?>
            </div>


            <?= Html::submitButton('Entrar', [
                'class' => 'w-full text-black bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5 text-center',
            ]) ?>

            <?php ActiveForm::end(); ?>

            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                Ainda não tem conta? <?= Html::a('Registar', ['site/signup'], ['class' => 'font-medium text-primary-600 hover:underline dark:text-primary-500']) ?>
            </p>
        </div>
    </div>
</section>