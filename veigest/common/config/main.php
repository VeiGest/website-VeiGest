<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],

        // SESSÃO PARTILHADA ENTRE FRONTEND E BACKEND
        'session' => [
            'class' => 'yii\web\Session',
            'name' => 'VeiGestSession',
            'cookieParams' => [
                'path' => '/',  
            ],
        ],

        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ],

];
