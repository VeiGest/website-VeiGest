<?php
return [
    'id' => 'app-frontend-tests',
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
        'mailer' => [
            'messageClass' => \yii\symfonymailer\Message::class
        ],
        'log' => [
            'traceLevel' => 3,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => __DIR__ . '/../runtime/logs/app.log',
                    'maxFileSize' => 10 * 1024 * 1024,
                    'maxLogFiles' => 5,
                ],
            ],
        ],
    ],
];
