<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Route $model */
/** @var array $drivers */
/** @var array $vehicles */

$this->title = 'Nova Rota';
$this->params['breadcrumbs'][] = ['label' => 'Rotas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="route-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', compact('model', 'drivers', 'vehicles')) ?>
</div>
