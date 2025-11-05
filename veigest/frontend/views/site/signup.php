<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\SignupForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Registar';
?>

<section class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center">
  <div class="w-full max-w-sm bg-white rounded-lg shadow dark:bg-gray-800 p-6">

      <div class="text-center mb-6">
          <img src="<?= Yii::getAlias('@web/images/veigestLogo.png') ?>" alt="VeiGest Logo" class="w-16 h-16 mx-auto mb-3">
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">VeiGest</h1>
          <p class="text-gray-500 dark:text-gray-400 text-sm">Crie a sua conta</p>
      </div>

      <?php $form = ActiveForm::begin(['id' => 'form-signup', 'options' => ['class' => 'space-y-4']]); ?>

          <?= $form->field($model, 'nome')->textInput([
              'autofocus' => true,
              'placeholder' => 'Nome de utilizador',
              'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white'
          ])->label(false) ?>

          <?= $form->field($model, 'email')->textInput([
              'placeholder' => 'Email',
              'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white'
          ])->label(false) ?>

          <?= $form->field($model, 'password')->passwordInput([
              'placeholder' => 'Palavra-passe',
              'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white'
          ])->label(false) ?>

          <?= Html::submitButton('Registar', [
              'class' => 'w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800',
              'name' => 'signup-button'
          ]) ?>

      <?php ActiveForm::end(); ?>

      <p class="text-sm text-gray-500 dark:text-gray-400 text-center mt-4">
          Já tem conta?
          <?= Html::a('Entrar', ['site/login'], ['class' => 'font-medium text-blue-600 hover:underline']) ?>
      </p>

  </div>
</section>
