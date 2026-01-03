<?php

use yii\helpers\Html;
use yii\grid\GridView;
use frontend\models\Driver;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Condutores';
$this->params['breadcrumbs'][] = $this->title;

// Mantém links clicáveis sem cor azul e mostra setas de ordenação
$this->registerCss('
.grid-view a { color: inherit; text-decoration: none; }
.grid-view a:hover { color: inherit; text-decoration: underline; }
.grid-view th a.asc:after { content: " \25B2"; font-size: 11px; }
.grid-view th a.desc:after { content: " \25BC"; font-size: 11px; }
');
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
                    [
                        'attribute' => 'id',
                        'format' => 'text',
                    ],
                    [
                        'attribute' => 'name',
                        'format' => 'text',
                        'label' => 'Nome',
                    ],
                    [
                        'attribute' => 'email',
                        'format' => 'text',
                    ],
                    [
                        'attribute' => 'phone',
                        'label' => 'Telefone',
                        'format' => 'text',
                    ],
                    [
                        'attribute' => 'license_number',
                        'label' => 'Número da Carta',
                        'format' => 'text',
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
