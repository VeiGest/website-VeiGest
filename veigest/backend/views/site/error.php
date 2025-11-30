<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception*/

use yii\helpers\Html;

$this->title = "Acesso Negado";
?>
<div class="text-center" style="margin-top: 120px;">

    <h1 style="font-size: 60px; color: #e3342f; font-weight: bold;">403</h1>

    <h2 class="mt-3" style="font-size: 28px;">Acesso Negado</h2>

    <p class="mt-3 text-muted">
        Não tem permissões para aceder a esta área do sistema.
    </p>

    <div class="mt-4">
        <?= Html::a('Voltar ao Início', Yii::getAlias('@frontendUrl'), [
            'class' => 'btn btn-primary px-4 py-2'
        ]) ?>
    </div>

</div>
