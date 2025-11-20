<?php

use yii\helpers\Html;

$this->title = 'Bem-vindo à VeiGest';
?>
<div class="site-index">
    <div class="jumbotron text-center bg-white mt-4">
        <h1 class="display-4" style="color: #02bda0;">Bem-vindo à VeiGest</h1>
        <p class="lead text-muted">Gestão de Frotas Empresariais</p>
    </div>

    <div class="body-content">
        <div class="row">

            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3">🚗</div>
                        <h4 class="fw-bold">Veículos</h4>
                        <p class="text-muted">Ver lista, adicionar veículos e gerir a frota.</p>
                        <?= Html::a('Ver Veículos', ['veiculos/index'], ['class' => 'btn btn-outline-primary mt-2']) ?>
                        <?= Html::a('Adicionar Veículo', ['veiculos/create'], ['class' => 'btn btn-primary mt-2 ms-2']) ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3">👥</div>
                        <h4 class="fw-bold">Condutores</h4>
                        <p class="text-muted">Gerir condutores, adicionar novos e monitorizar.</p>
                        <?= Html::a('Ver Condutores', ['condutores/index'], ['class' => 'btn btn-outline-primary mt-2']) ?>
                        <?= Html::a('Adicionar Condutor', ['condutores/create'], ['class' => 'btn btn-primary mt-2 ms-2']) ?>
                    </div>
                </div>
            </div>

            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->hasRole('admin')): ?>
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3">🔧</div>
                        <h4 class="fw-bold">Gestão de Utilizadores</h4>
                        <p class="text-muted">Painel administrativo para gerir utilizadores.</p>
                        <?= Html::a('Ir para Backoffice', 'http://localhost/website-VeiGest/veigest/backend/web/index.php?r=user/index', ['class' => 'btn btn-dark mt-2', 'target' => '_blank']) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
