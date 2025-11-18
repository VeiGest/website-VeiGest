<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>
    <header>
        <?php
        NavBar::begin([
            'brandLabel' => '<span style="display:flex; align-items:center;">
    <img src="' . Yii::getAlias('@web/images/veigest-logo.png') . '" style="height:34px; margin-right:8px;">
    <span style="font-weight:600; font-size:20px; color:white;">VeiGest</span>
</span>',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar navbar-expand-lg navbar-dark shadow-sm',
                'style' => 'background-color:#0f1115; padding:10px 0;'
            ],
        ]);

        $menuItems = [
            ['label' => 'Home', 'url' => ['/site/index']],

            [
                'label' => 'Veículos',
                'items' => [
                    ['label' => 'Lista de Veículos', 'url' => ['/veiculos/index']],
                    ['label' => 'Adicionar Veículo', 'url' => ['/veiculos/create']],
                ],
            ],

            [
                'label' => 'Condutores',
                'items' => [
                    ['label' => 'Lista de Condutores', 'url' => ['/condutores/index']],
                    ['label' => 'Adicionar Condutor', 'url' => ['/condutores/create']],
                ],
            ],
        ];

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav me-auto mb-2 mb-lg-0'],
            'items' => $menuItems,
            'encodeLabels' => false,
        ]);

        if (Yii::$app->user->isGuest) {

            echo Html::a('Login', ['/site/login'], [
                'class' => 'btn btn-outline-light ms-2'
            ]);
        } else {

            echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->nome . ')',
                    ['class' => 'btn btn-danger ms-3']
                )
                . Html::endForm();
        }

        NavBar::end();
        ?>
    </header>




    <main role="main" class="flex-shrink-0">
        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>

    <footer class="footer mt-auto py-3 text-muted">
        <div class="container">
            <p class="float-start">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
            <p class="float-end"><?= Yii::powered() ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage();
