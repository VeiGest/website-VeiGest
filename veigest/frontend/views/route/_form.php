<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Route $model */
/** @var array $drivers */
/** @var array $vehicles */

?>

<div class="route-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'driver_id')->dropDownList($drivers, ['prompt' => 'Selecione o condutor']) ?>
    <?= $form->field($model, 'vehicle_id')->dropDownList($vehicles, ['prompt' => 'Selecione o veiculo']) ?>

    <?= $form->field($model, 'start_time')->input('datetime-local') ?>
    <?= $form->field($model, 'start_location')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'end_location')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Criar' : 'Guardar', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
