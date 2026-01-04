<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\Vehicle;

/** @var yii\web\View $this */
/** @var frontend\models\Vehicle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="vehicle-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group row">
        <div class="col-md-6">
            <?= $form->field($model, 'license_plate')->textInput(['maxlength' => true, 'placeholder' => 'Ex: AA-00-AA', 'class' => 'form-control']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList(
                Vehicle::optsStatus(),
                ['prompt' => 'Selecione o estado...', 'class' => 'form-control']
            ) ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <?= $form->field($model, 'brand')->textInput(['maxlength' => true, 'placeholder' => 'Ex: Toyota', 'class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'model')->textInput(['maxlength' => true, 'placeholder' => 'Ex: Corolla', 'class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'year')->textInput(['placeholder' => 'Ex: 2024', 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-6">
            <?= $form->field($model, 'fuel_type')->dropDownList(
                Vehicle::optsFuelType(),
                ['prompt' => 'Selecione o combustível...', 'class' => 'form-control']
            ) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'mileage')->textInput(['placeholder' => 'Ex: 50000 km', 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-12">
            <?= Html::submitButton(
                $model->isNewRecord ? 'Criar Veículo' : 'Atualizar Veículo',
                ['class' => $model->isNewRecord ? 'btn btn-success btn-lg btn-block' : 'btn btn-primary btn-lg btn-block']
            ) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
