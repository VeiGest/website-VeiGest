<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use frontend\models\Driver;

/** @var yii\web\View $this */
/** @var frontend\models\Driver $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Condutores', 'url' => ['dashboard/drivers']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-header">
    <div class="row justify-content-center mb-2">
        <div class="col-12 col-xl-10">
            <h1 class="m-0" style="margin-left: 0.25rem;"><?= Html::encode($this->title) ?></h1>
        </div>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card" style="box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div class="card-header" style="background-color: var(--primary-color); color: white; border-bottom: 3px solid var(--dark-color);">
                    <h3 class="card-title mb-0"><i class="fas fa-user"></i> Detalhes do Condutor</h3>
                    <div class="card-tools">
                        <?php if (Yii::$app->user->can('drivers.update')): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('drivers.delete')): ?>
                            <?= Html::a('Apagar', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => [
                                    'confirm' => 'Tem a certeza que deseja apagar este condutor?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'name',
                            'email:email',
                            'phone',
                            'license_number',
                            [
                                'attribute' => 'license_expiry',
                                'format' => ['date', 'php:d/m/Y'],
                                'label' => 'Validade da Carta',
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Estado',
                                'value' => function($model) {
                                    $isActive = $model->status === Driver::STATUS_ACTIVE;
                                    $status = $isActive ? 'Ativo' : 'Inativo';
                                    $badgeClass = $isActive ? 'badge-success' : 'badge-secondary';
                                    return '<span class="badge ' . $badgeClass . '">' . $status . '</span>';
                                },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'created_at',
                                'format' => ['date', 'php:d/m/Y H:i:s'],
                                'label' => 'Criado em',
                            ],
                            [
                                'attribute' => 'updated_at',
                                'format' => ['date', 'php:d/m/Y H:i:s'],
                                'label' => 'Atualizado em',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
