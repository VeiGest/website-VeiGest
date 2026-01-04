<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\Driver;

/** @var yii\web\View $this */
/** @var frontend\models\Driver $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="driver-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Ex: João Silva', 'class' => 'form-control'])->label('Nome') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Ex: joao@email.com', 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-6">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => 'Ex: +351 91 1234567', 'class' => 'form-control'])->label('Telefone') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'license_number')->textInput(['maxlength' => true, 'placeholder' => 'Ex: 123456789', 'class' => 'form-control'])->label('Número da Carta') ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-6">
            <?= $form->field($model, 'license_expiry')->textInput(['type' => 'date', 'class' => 'form-control'])->label('Validade da Carta') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList(
                Driver::optsStatus(),
                ['prompt' => 'Selecione o estado...', 'class' => 'form-control']
            ) ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-12">
            <?= $form->field($model, 'password')->passwordInput(['placeholder' => $model->isNewRecord ? 'Palavra-passe' : 'Deixe em branco para manter a atual', 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-12">
            <?= Html::submitButton(
                $model->isNewRecord ? 'Criar Condutor' : 'Atualizar Condutor',
                ['class' => $model->isNewRecord ? 'btn btn-success btn-lg btn-block' : 'btn btn-primary btn-lg btn-block']
            ) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
