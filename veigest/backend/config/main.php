<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],

    'as access' => [
    'class' => \yii\filters\AccessControl::class,
    'ruleConfig' => [
        'class' => \yii\filters\AccessRule::class,
    ],
    'except' => ['error', 'login', 'api/*'], // Excluir todas as rotas da API
    'rules' => [

        [
            'allow' => true,
            'actions' => ['login', 'error'], 
        ],

        [
            'allow' => true,
            'roles' => ['acessoBackend'],
        ],
    ],
],


    'modules' => [
        'api' => [
            'class' => 'backend\modules\api\Module',
        ],
    ],
    'components' => [

        'errorHandler' => [
            'errorAction' => 'site/error',
    ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'cookieValidationKey' => 'Yup8MeyEmKivPSYV944gTuoRjBGqKkVt',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],

       //usa a mesma sessao do front
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity',   
                'httpOnly' => true,
                'path' => '/',           
            ],
        ],

        
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],

        // urlManager para API do backend
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // Authentication endpoints
                'POST api/auth/login' => 'api/auth/login',
                'POST api/auth/logout' => 'api/auth/logout',
                'GET api/auth/me' => 'api/auth/me',
                'POST api/auth/refresh' => 'api/auth/refresh',
                
                // REST API routes
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/vehicle'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/user'], 'pluralize' => false],
            ],
        ],
    ],
    'params' => $params,
];
