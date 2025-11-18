<?php

/** @var \yii\web\View $this */

use yii\helpers\Html;

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title ?: 'VeiGest') ?></title>

    <!-- Tailwind / Flowbite CDN (podes trocar por ficheiros locais se preferires) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.js"></script>

    <?php $this->head() ?>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <?php $this->beginBody() ?>

    <!-- A área de conteúdo onde a view 'site/login' vai ser renderizada -->
    <?= $content ?>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage(); ?>