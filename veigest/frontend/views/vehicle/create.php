<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Vehicle $model */

$this->title = 'Criar Veículo';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Veículos', 'url' => ['dashboard/vehicles']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 justify-content-center text-center">
                <div class="col-12">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <?= Html::encode($this->title) ?>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content d-flex justify-content-center" style="padding: 0 16px;">
        <div class="card w-100" style="max-width: min(960px, calc(100vw - 340px)); margin: 0 auto; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <div class="card-header" style="background-color: var(--primary-color); color: white; border-bottom: 3px solid var(--dark-color);">
                <h3 class="card-title mb-0 text-center">
                    <i class="fas fa-plus-circle mr-2"></i><?= Html::encode($this->title) ?>
                </h3>
            </div>
            <div class="card-body p-5">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </section>
</div>
