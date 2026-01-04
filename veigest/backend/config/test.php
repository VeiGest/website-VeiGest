<?php
return [
    'id' => 'app-backend-tests',
    'components' => [
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=veigest',
        ],
    ],
];
