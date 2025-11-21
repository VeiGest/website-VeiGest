<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VeiGest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #09BC8A;
            --color-onyx: #3C3C3C;
            --color-turquoise: #75DDDD;
        }
        
        .bg-primary { background-color: var(--color-primary); }
        .text-primary { color: var(--color-primary); }
        
        .input-focus:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(9, 188, 138, 0.1);
        }
    </style>
</head>
<body class="bg-white">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center space-x-2 mb-4">
                    <img src="/images/veigest-logo.png" alt="VeiGest" class="h-12 w-12">
                    <span class="text-2xl font-bold text-primary">VeiGest</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Aceder ao Sistema</h1>
                <p class="text-gray-600 mt-2">Gestão Inteligente de Frotas</p>
            </div>

            <!-- Login Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                ]); ?>
                <form class="space-y-6">
                    <!-- Email -->
                    <div>
                        <?= $form->field($model, 'nome')->textInput([
                            'autofocus' => true,
                            'class' => 'bg-white border border-gray-300 rounded-lg block w-full p-2.5 placeholder-gray-400 text-gray-900',
                            'placeholder' => 'Nome de Utilizador ou Email',
                        ])->label('Nome de Utilizador', ['class' => 'text-sm font-medium text-gray-900']) ?>
                    </div>

                    <!-- Password -->
                    <div>
                        <?= $form->field($model, 'password')->passwordInput([
                            'class' => 'bg-white border border-gray-300 rounded-lg block w-full p-2.5 placeholder-gray-400 text-gray-900',
                            'placeholder' => '••••••••',
                        ])->label('Palavra-passe', ['class' => 'text-sm font-medium text-gray-900']) ?>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between">
                        <div>
                            <?= $form->field($model, 'rememberMe')->checkbox()->label('Lembrar-me') ?>
                        </div>
                        <div>
                            <?= Html::a('Esqueceu a senha?', ['site/request-password-reset'], ['class' => 'text-sm font-medium text-primary-600 hover:underline dark:text-primary-500']) ?>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <div>
                        <?= Html::submitButton('Entrar', [
                            'class' => 'w-full text-black bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5 text-center',
                        ]) ?>
                    </div>
                </form>
                <?php ActiveForm::end(); ?>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Não tem conta?</span>
                    </div>
                </div>

                <!-- Sign Up -->
                <a href="#" class="block w-full text-center border-2 border-primary text-primary py-3 rounded-lg font-bold hover:bg-primary hover:bg-opacity-5 transition">
                    Criar Conta
                </a>
            </div>

            <!-- Info Cards -->
            <div class="mt-8 grid grid-cols-3 gap-4">
                <div class="bg-white rounded-lg p-4 text-center">
                    <i class="fas fa-lock text-2xl text-primary mb-2"></i>
                    <p class="text-xs text-gray-600">Segurança em Primeiro Lugar</p>
                </div>
                <div class="bg-white rounded-lg p-4 text-center">
                    <i class="fas fa-mobile-alt text-2xl text-primary mb-2"></i>
                    <p class="text-xs text-gray-600">Acesso Móvel</p>
                </div>
                <div class="bg-white rounded-lg p-4 text-center">
                    <i class="fas fa-headset text-2xl text-primary mb-2"></i>
                    <p class="text-xs text-gray-600">Suporte 24/7</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-sm text-gray-600">
                <p>Problemas ao aceder? <a href="#" class="text-primary hover:underline">Contacte o suporte</a></p>
            </div>
        </div>
    </div>
</body>
</html>
