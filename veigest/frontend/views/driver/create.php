<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Driver $model */

$this->title = 'Criar Condutor';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Condutores', 'url' => ['dashboard/drivers']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card" style="box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="card-header" style="background-color: var(--primary-color); color: white; border-bottom: 3px solid var(--dark-color);">
                            <h3 class="card-title mb-0"><i class="fas fa-user-plus"></i> <?= Html::encode($this->title) ?></h3>
                        </div>
                        <div class="card-body p-5">
                            <?= $this->render('_form', [
                                'model' => $model,
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
