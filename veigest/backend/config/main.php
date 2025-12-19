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
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/company'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/maintenance'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/fuel-log'], 'pluralize' => false],
                
                // Custom endpoints for companies
                'GET api/companies/<id:\d+>/vehicles' => 'api/company/vehicles',
                'GET api/companies/<id:\d+>/users' => 'api/company/users',
                'GET api/companies/<id:\d+>/stats' => 'api/company/stats',
                
                // Custom endpoints for vehicles  
                'GET api/vehicles/<id:\d+>/maintenances' => 'api/vehicle/maintenances',
                'GET api/vehicles/<id:\d+>/fuel-logs' => 'api/vehicle/fuel-logs',
                'GET api/vehicles/<id:\d+>/stats' => 'api/vehicle/stats',
                'GET api/vehicles/by-status/<status:\w+>' => 'api/vehicle/by-status',
                
                // Custom endpoints for maintenance
                'GET api/maintenance/by-vehicle/<vehicle_id:\d+>' => 'api/maintenance/by-vehicle',
                'GET api/maintenance/by-status/<estado:\w+>' => 'api/maintenance/by-status',
                'POST api/maintenance/<id:\d+>/schedule' => 'api/maintenance/schedule',
                'GET api/maintenance/reports/monthly' => 'api/maintenance/reports-monthly',
                'GET api/maintenance/reports/costs' => 'api/maintenance/reports-costs',
                'GET api/maintenance/stats' => 'api/maintenance/stats',
                
                // Custom endpoints for fuel logs
                'GET api/fuel-logs/by-vehicle/<vehicle_id:\d+>' => 'api/fuel-log/by-vehicle',
                'GET api/fuel-logs/stats' => 'api/fuel-log/stats',
                'GET api/fuel-logs/alerts' => 'api/fuel-log/alerts',
                'GET api/fuel-logs/efficiency-report' => 'api/fuel-log/efficiency-report',
                
                // Custom endpoints for users
                'GET api/users/drivers' => 'api/user/drivers',
                'GET api/users/profile' => 'api/user/profile',
                'GET api/users/by-company/<company_id:\d+>' => 'api/user/by-company',
                'POST api/users/<id:\d+>/update-photo' => 'api/user/update-photo',
            ],
        ],
    ],
    'params' => $params,
];
