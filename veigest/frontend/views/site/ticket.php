<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\TicketForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Sistema de Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="bg-gray-50">

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-500 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Sistema de Tickets de Suporte</h1>
            <p class="text-lg text-opacity-90">Crie um ticket para receber assistência personalizada da nossa equipe</p>
        </div>
    </section>

    <!-- Support Options -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-ticket-alt text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Criar Ticket</h3>
                    <p class="text-gray-600 mb-4">Abra um ticket detalhado para suporte técnico</p>
                    <span class="text-primary font-medium">Você está aqui</span>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-clock text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Acompanhamento</h3>
                    <p class="text-gray-600 mb-4">Acompanhe o status do seu ticket em tempo real</p>
                    <a href="#" class="text-primary hover:underline font-medium">Ver Meus Tickets</a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-question-circle text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">FAQ</h3>
                    <p class="text-gray-600 mb-4">Consulte nossas perguntas frequentes</p>
                    <a href="#" class="text-primary hover:underline font-medium">Ver FAQ</a>
                </div>
            </div>

            <!-- Ticket Form -->
            <div class="bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Criar Novo Ticket</h2>
                <?php $form = ActiveForm::begin(['id' => 'ticket-form']); ?>
                <div class="space-y-4">
                    <?= $form->field($model, 'name')->textInput([
                        'autofocus' => true,
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Seu nome completo'
                    ])->label('Nome', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'email')->textInput([
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'seu.email@empresa.com'
                    ])->label('Email', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <div class="grid md:grid-cols-2 gap-4">
                        <?= $form->field($model, 'priority')->dropDownList([
                            'low' => 'Baixa',
                            'medium' => 'Média',
                            'high' => 'Alta',
                            'urgent' => 'Urgente'
                        ], [
                            'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                            'prompt' => 'Selecione a prioridade'
                        ])->label('Prioridade', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                        <?= $form->field($model, 'category')->dropDownList([
                            'technical' => 'Problema Técnico',
                            'billing' => 'Faturamento',
                            'account' => 'Conta/Usuário',
                            'feature' => 'Solicitação de Funcionalidade',
                            'other' => 'Outro'
                        ], [
                            'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                            'prompt' => 'Selecione a categoria'
                        ])->label('Categoria', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>
                    </div>

                    <?= $form->field($model, 'subject')->textInput([
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Título breve do problema'
                    ])->label('Assunto', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'body')->textarea([
                        'rows' => 6,
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Descreva detalhadamente o seu problema ou solicitação'
                    ])->label('Descrição', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                        'template' => '<div class="mb-4"><div class="flex items-center space-x-4">{image}{input}</div></div>',
                        'options' => ['class' => 'px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary']
                    ])->label('Código de Verificação', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Criar Ticket', [
                            'class' => 'w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-opacity-90 transition',
                            'name' => 'ticket-button'
                        ]) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </section>

</div>