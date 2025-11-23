<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\User $model */

// título com o username em vez do id
$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Utilizadores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Tem a certeza que quer eliminar este utilizador?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            // campos essenciais
            'username',
            'nome',
            'email:email',
            'role',
            'estado',
            'company_id',

            // datas (convertendo timestamp para formato legível)
            [
                'attribute' => 'created_at',
                'label' => 'Created At',
                'value' => function($m) {
                    return $m->created_at ? date('Y-m-d H:i:s', $m->created_at) : null;
                },
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Updated At',
                'value' => function($m) {
                    return $m->updated_at ? date('Y-m-d H:i:s', $m->updated_at) : null;
                },
            ],
        ],
    ]) ?>

</div>
