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
    'modules' => [
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'cookieValidationKey' => 'Yup8MeyEmKivPSYV944gTuoRjBGqKkVt',
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

        // action para erros
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        // urlManager para API do backend (exemplos)
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // Rotas REST (AINDA NAO TERMINADO)
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
            ],
        ],
    ],
    'params' => $params,
];
