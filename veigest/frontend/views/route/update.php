<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Route $model */
/** @var array $drivers */
/** @var array $vehicles */

$this->title = 'Editar Rota: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Rotas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="route-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', compact('model', 'drivers', 'vehicles')) ?>
</div>
