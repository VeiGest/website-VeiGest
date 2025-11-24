<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Dashboard asset bundle
 */
class DashboardAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        // CSS files will be included in the layout
    ];
    public $js = [
        // JS files will be included in the layout
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}