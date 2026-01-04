<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Veículos';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="vehicle-index">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Veículos</h3>
            <div class="card-tools">
                <?php if (Yii::$app->user->can('vehicles.create')) { ?>
                    <?= Html::a('Criar Veículo', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
                <?php } ?>
            </div>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'license_plate',
                    [
                        'attribute' => 'brand',
                        'value' => function($model) {
                            return $model->brand . ' ' . $model->model;
                        },
                        'label' => 'Modelo',
                    ],
                    'year',
                    [
                        'attribute' => 'status',
                        'value' => function($model) {
                            $statuses = ['ativo' => 'Ativo', 'inativo' => 'Inativo', 'manutencao' => 'Manutenção'];
                            return $statuses[$model->status] ?? $model->status;
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'view' => function($url, $model, $key) {
                                if (!Yii::$app->user->can('vehicles.view')) {
                                    return '';
                                }
                                return Html::a('Ver', $url, ['class' => 'btn btn-info btn-sm']);
                            },
                            'update' => function($url, $model, $key) {
                                if (!Yii::$app->user->can('vehicles.update')) {
                                    return '';
                                }
                                return Html::a('Editar', $url, ['class' => 'btn btn-warning btn-sm']);
                            },
                            'delete' => function($url, $model, $key) {
                                if (!Yii::$app->user->can('vehicles.delete')) {
                                    return '';
                                }
                                return Html::a('Apagar', $url, [
                                    'class' => 'btn btn-danger btn-sm',
                                    'data' => [
                                        'confirm' => 'Tem certeza?',
                                        'method' => 'post',
                                    ],
                                ]);
                            },
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
