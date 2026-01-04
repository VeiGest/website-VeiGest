<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Rotas';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
.grid-view a { color: inherit; text-decoration: none; }
.grid-view a:hover { color: inherit; text-decoration: underline; }
.grid-view th a.asc:after { content: " \25B2"; font-size: 11px; }
.grid-view th a.desc:after { content: " \25BC"; font-size: 11px; }
');
?>

<div class="content-header">
    <div class="row justify-content-center mb-2">
        <div class="col-12 col-xl-11 col-xxl-10 d-flex align-items-center justify-content-between flex-wrap">
            <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
            <?php if (Yii::$app->user->can('routes.create')): ?>
                <?= Html::a('Nova Rota', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11 col-xxl-10">
            <div class="card w-100" style="max-width: 1200px; margin: 0 auto;">
                <div class="card-header">
                    <h3 class="card-title">Lista de Rotas</h3>
                </div>
                <div class="card-body p-0">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => '<div class="pl-3 pt-2">Mostrando <strong>{begin}-{end}</strong> de <strong>{totalCount}</strong> itens.</div>',
                        'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                        'headerRowOptions' => ['style' => 'background:#f7f9fb;'],
                        'columns' => [
                            ['attribute' => 'id', 'format' => 'text', 'contentOptions' => ['style' => 'width:70px; font-weight:600;']],
                            ['attribute' => 'start_time', 'label' => 'Data/Hora', 'format' => 'datetime', 'contentOptions' => ['style' => 'min-width:160px;']],
                            [
                                'label' => 'Condutor',
                                'value' => function ($model) { return $model->driver ? $model->driver->name : '-'; },
                                'format' => 'text',
                            ],
                            [
                                'label' => 'Veiculo',
                                'value' => function ($model) { return $model->vehicle ? $model->vehicle->license_plate : '-'; },
                                'format' => 'text',
                            ],
                            ['attribute' => 'start_location', 'label' => 'Origem', 'format' => 'text'],
                            ['attribute' => 'end_location', 'label' => 'Destino', 'format' => 'text'],
                            [
                                'class' => 'yii\\grid\\ActionColumn',
                                'header' => 'Acoes',
                                'template' => '{view} {update} {delete}',
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        return Yii::$app->user->can('routes.view')
                                            ? Html::a('Ver', $url, ['class' => 'btn btn-outline-info btn-sm me-1'])
                                            : '';
                                    },
                                    'update' => function ($url, $model) {
                                        return Yii::$app->user->can('routes.update')
                                            ? Html::a('Editar', $url, ['class' => 'btn btn-outline-warning btn-sm me-1'])
                                            : '';
                                    },
                                    'delete' => function ($url, $model) {
                                        return Yii::$app->user->can('routes.delete')
                                            ? Html::a('Apagar', $url, [
                                                'class' => 'btn btn-outline-danger btn-sm',
                                                'data' => [
                                                    'confirm' => 'Tem a certeza que deseja apagar esta rota?',
                                                    'method' => 'post',
                                                ],
                                            ])
                                            : '';
                                    },
                                ],
                            ],
                        ],
                    ]) ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
