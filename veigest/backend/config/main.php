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
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
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
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
            'urlManager' => [
                'enablePrettyUrl' => true,
                'showScriptName' => false,
                'enableStrictParsing' => false,
                'rules' => [
                    // API v1 routes
                    ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/v1/company'], 'pluralize' => false,
                        'extraPatterns' => [
                            'GET {id}/vehicles' => 'vehicles',
                            'GET {id}/users' => 'users', 
                            'GET {id}/stats' => 'stats',
                        ]
                    ],
                    ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/v1/vehicle'], 'pluralize' => false,
                        'extraPatterns' => [
                            'GET {id}/maintenances' => 'maintenances',
                            'GET {id}/fuel-logs' => 'fuel-logs',
                            'GET {id}/stats' => 'stats',
                            'GET company/{company_id}' => 'by-company',
                            'GET status/{status}' => 'by-status',
                        ]
                    ],
                    ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/v1/maintenance'], 'pluralize' => false,
                        'extraPatterns' => [
                            'GET vehicle/{vehicle_id}' => 'by-vehicle',
                            'GET status/{status}' => 'by-status',
                            'GET stats' => 'stats',
                        ]
                    ],
                    ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/v1/user'], 'pluralize' => false,
                        'extraPatterns' => [
                            'GET company/{company_id}' => 'by-company',
                            'GET drivers' => 'drivers',
                            'GET profile' => 'profile',
                        ]
                    ],
                    ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/v1/auth'], 'pluralize' => false,
                        'extraPatterns' => [
                            'POST login' => 'login',
                            'POST refresh' => 'refresh',
                            'POST logout' => 'logout',
                        ]
                    ],
                    ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/v1/messaging'], 'pluralize' => false,
                        'extraPatterns' => [
                            'GET events' => 'events',
                            'GET subscribe' => 'subscribe',
                            'POST publish' => 'publish',
                            'GET stats' => 'stats',
                        ]
                    ],
                ],
            ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];
