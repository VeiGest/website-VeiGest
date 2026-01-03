<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\models\Route $model */

$this->title = 'Rota #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Rotas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="route-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (Yii::$app->user->can('routes.update')): ?>
            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?php endif; ?>
        <?php if (Yii::$app->user->can('routes.delete')): ?>
            <?= Html::a('Apagar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Tem a certeza que deseja apagar esta rota?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'start_time:datetime',
            [
                'label' => 'Condutor',
                'value' => $model->driver ? $model->driver->name : '-',
            ],
            [
                'label' => 'Veiculo',
                'value' => $model->vehicle ? $model->vehicle->license_plate : '-',
            ],
            'start_location',
            'end_location',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>
