<?php
return [
    'id' => 'app-backend-tests',
    'components' => [
        'assetManager' => [
            'basePath' => '@runtime/assets',
            'baseUrl' => '/assets',
            'appendTimestamp' => false,
        ],
        'urlManager' => [
            'showScriptName' => true,
            'enablePrettyUrl' => false,
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'scriptUrl' => '/index-test.php',
            'scriptFile' => __DIR__ . '/../web/index-test.php',
            'hostInfo' => 'http://localhost',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'csrfCookie' => [
                'path' => '/',
                'domain' => 'localhost',
                'httpOnly' => true,
            ],
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'name' => 'VeiGestSessionTest',
            'cookieParams' => [
                'path' => '/',
                'domain' => 'localhost',
                'httpOnly' => true,
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity-test',
                'httpOnly' => true,
                'path' => '/',
                'domain' => 'localhost',
            ],
        ],
        // DB config is in common/config/test-local.php
    ],
];
