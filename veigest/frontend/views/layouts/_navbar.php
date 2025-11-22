<?php

use yii\bootstrap5\Html;
?>
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <!-- LOGO -->
            <div class="flex items-center space-x-2">
                <img src="./images/veigest-logo.png" alt="VeiGest Logo" class="h-10 w-10">
                <span class="text-xl font-bold text-primary">VeiGest</span>
            </div>

            <!-- MENU -->
            <div class="hidden md:flex space-x-8">
                <a href="#" class="text-gray-700 hover:text-primary transition">Home</a>
                <a href="#services" class="text-gray-700 hover:text-primary transition">Serviços</a>
                <a href="#beneficios" class="text-gray-700 hover:text-primary transition">Benefícios</a>
                <a href="#contacto" class="text-gray-700 hover:text-primary transition">Contacto</a>
            </div>

            <!-- AUTENTICAÇÃO -->
            <div class="flex items-center space-x-4">

                <?php if (Yii::$app->user->isGuest): ?>

                    <?= Html::a('Login', ['/site/login'], [
                        'class' => 'text-gray-700 hover:text-primary font-medium px-4 py-2 rounded transition',
                    ]) ?>

                    <?= Html::a('Registar', ['/site/signup'], [
                        'class' => 'btn-primary px-6 py-2 rounded-lg font-medium hover:opacity-90',
                    ]) ?>

                <?php else: ?>

                    <!-- BOTÃO BACKOFFICE SE FOR ADMIN -->
                    <?php if (Yii::$app->user->can('admin')): ?>
                        <a href="<?= Yii::getAlias('@backendUrl') ?>"
                           class="px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-green-600 transition">
                            Backoffice
                        </a>
                    <?php endif; ?>

                    <!-- LOGOUT -->
                    <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'inline']) .
                        Html::submitButton(
                            'Logout (' . Yii::$app->user->identity->username . ')',
                            ['class' => 'btn btn-danger px-4 py-2 rounded font-medium']
                        ) .
                        Html::endForm();
                    ?>

                <?php endif; ?>

            </div>
        </div>
    </div>
</nav>
