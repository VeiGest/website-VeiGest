<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="bg-gray-50">
    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20 md:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <i class="fas fa-exclamation-triangle text-6xl mb-6"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                <?= Html::encode($this->title) ?>
            </h1>
            <p class="text-lg mb-8 text-opacity-90">
                Ocorreu um erro inesperado. Pedimos desculpa pelo inconveniente.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="bg-white text-primary px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition text-center">
                    Voltar ao Início
                </a>
                <button onclick="history.back()" class="border-2 border-white text-white px-8 py-3 rounded-lg font-bold hover:bg-white hover:bg-opacity-10 transition">
                    Tentar Novamente
                </button>
            </div>
        </div>
    </section>

    <!-- Error Details Section -->
    <section class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Detalhes do Erro</h2>
                <p class="text-xl text-gray-600">Informações técnicas sobre o problema ocorrido</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-8 card-shadow">
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Mensagem de Erro</h3>
                    <div class="bg-white border border-gray-300 rounded-lg p-4">
                        <p class="text-gray-800 font-mono text-sm">
                            <?= nl2br(Html::encode($message)) ?>
                        </p>
                    </div>
                </div>
                <?php if (YII_ENV_DEV): ?>
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Informações Técnicas (Desenvolvimento)</h3>
                        <div class="bg-white border border-gray-300 rounded-lg p-4">
                            <pre class="text-gray-800 font-mono text-sm overflow-x-auto">
<?= Html::encode($exception->getTraceAsString()) ?>
                        </pre>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="text-center">
                    <p class="text-gray-600 mb-4">
                        Se o problema persistir, entre em contacto com o nosso suporte técnico.
                    </p>
                    <!-- <a href="/site/contact" class="inline-block bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-opacity-90 transition">
                        Contactar Suporte
                    </a> -->
                    <?= Html::a('Contactos', ['/site/contact'], [
                    'class' => 'inline-block bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-opacity-90 transition',
                    ]) ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Options -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Precisa de Ajuda?</h2>
                <p class="text-xl text-gray-600">Estamos aqui para ajudar</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="service-card bg-white rounded-lg p-8 card-shadow border-t-4 border-primary text-center">
                    <div class="mb-4">
                        <i class="fas fa-envelope text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Email</h3>
                    <p class="text-gray-600 mb-4">Envie-nos um email e responderemos dentro de 24 horas</p>
                    <a href="mailto:support@veigest.com" class="text-primary hover:underline font-medium">support@veigest.com</a>
                </div>

                <div class="service-card bg-white rounded-lg p-8 card-shadow border-t-4 border-primary text-center">
                    <div class="mb-4">
                        <i class="fas fa-phone text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Telefone</h3>
                    <p class="text-gray-600 mb-4">Ligue-nos durante o horário comercial (9h-17h)</p>
                    <a href="tel:+351210000000" class="text-primary hover:underline font-medium">+351 21 0000 000</a>
                </div>

                <div class="service-card bg-white rounded-lg p-8 card-shadow border-t-4 border-primary text-center">
                    <div class="mb-4">
                        <i class="fas fa-comments text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Chat ao Vivo</h3>
                    <p class="text-gray-600 mb-4">Conversa instantânea com nossos especialistas</p>
                    <a href="#" class="text-primary hover:underline font-medium">Iniciar Chat</a>
                </div>
            </div>
        </div>
    </section>

</div>