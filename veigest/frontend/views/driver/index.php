<?php

use yii\helpers\Html;
use yii\grid\GridView;
use frontend\models\Driver;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Condutores';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="driver-index">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Condutores</h3>
            <?php if (Yii::$app->user->can('drivers.create')): ?>
                <div class="card-tools">
                    <?= Html::a('Novo Condutor', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'id',
                    'nome',
                    'email:email',
                    'telefone',
                    'numero_carta',
                    [
                        'attribute' => 'estado',
                        'value' => function($model) {
                            $status = $model->estado === Driver::STATUS_ACTIVE ? 'Ativo' : 'Inativo';
                            $badgeClass = $model->estado === Driver::STATUS_ACTIVE ? 'badge-success' : 'badge-secondary';
                            return '<span class="badge ' . $badgeClass . '">' . $status . '</span>';
                        },
                        'format' => 'html',
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Ações',
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                if (Yii::$app->user->can('drivers.view')) {
                                    return Html::a('Ver', $url, ['class' => 'btn btn-info btn-sm']);
                                }
                                return '';
                            },
                            'update' => function ($url, $model, $key) {
                                if (Yii::$app->user->can('drivers.update')) {
                                    return Html::a('Editar', $url, ['class' => 'btn btn-warning btn-sm']);
                                }
                                return '';
                            },
                            'delete' => function ($url, $model, $key) {
                                if (Yii::$app->user->can('drivers.delete')) {
                                    return Html::a('Apagar', $url, [
                                        'class' => 'btn btn-danger btn-sm',
                                        'data' => [
                                            'confirm' => 'Tem a certeza que deseja apagar este condutor?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                }
                                return '';
                            },
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
