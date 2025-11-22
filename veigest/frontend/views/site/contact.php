<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\ContactForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Contact';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="bg-gray-50">

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-500 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Centro de Suporte</h1>
            <p class="text-lg text-opacity-90">Estamos aqui para ajudar. Contacte-nos a qualquer momento</p>
        </div>
    </section>

    <!-- Support Options -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-envelope text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Email</h3>
                    <p class="text-gray-600 mb-4">Envie-nos um email e responderemos dentro de 24 horas</p>
                    <a href="mailto:support@veigest.com" class="text-primary hover:underline font-medium">support@veigest.com</a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-phone text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Telefone</h3>
                    <p class="text-gray-600 mb-4">Ligue-nos durante o horário comercial (9h-17h)</p>
                    <a href="tel:+351210000000" class="text-primary hover:underline font-medium">+351 21 0000 000</a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-comments text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Chat ao Vivo</h3>
                    <p class="text-gray-600 mb-4">Conversa instantânea com nossos especialistas</p>
                    <a href="#" class="text-primary hover:underline font-medium">Iniciar Chat</a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Formulário de Contacto</h2>
                <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>
                <div class="space-y-4">
                    <?= $form->field($model, 'name')->textInput([
                        'autofocus' => true,
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Seu nome'
                    ])->label('Nome', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'email')->textInput([
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'seu.email@empresa.com'
                    ])->label('Email', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'subject')->textInput([
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Qual é o tema?'
                    ])->label('Assunto', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'body')->textarea([
                        'rows' => 5,
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Descreva o seu problema'
                    ])->label('Mensagem', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                        'template' => '<div class="mb-4"><div class="flex items-center space-x-4">{image}{input}</div></div>',
                        'options' => ['class' => 'px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary']
                    ])->label('Código de Verificação', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Enviar Mensagem', [
                            'class' => 'w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-opacity-90 transition',
                            'name' => 'contact-button'
                        ]) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </section>


