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
    // bootstrap log (pode adicionar 'debug'/'gii' via main-local em dev)
    'bootstrap' => ['log'],
    'modules' => [
        // adicione módulos do backend aqui (gii em dev via main-local)
    ],
    'components' => [
        // request: CSRF separado do frontend (boa prática), cookieValidationKey próprio
        'request' => [
            'csrfParam' => '_csrf-backend',
            'cookieValidationKey' => 'Yup8MeyEmKivPSYV944gTuoRjBGqKkVt',
        ],

        // user: usa o mesmo identityClass do common
        // IMPORTANTE: a cookie de identidade tem o MESMO NOME do frontend ('_identity')
        // e path '/' para que o backend consiga ler a sessão / cookie do frontend quando necessário.
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity',   // mesmo nome que no frontend -> partilha de login
                'httpOnly' => true,
                'path' => '/',           // essencial para partilha entre apps
            ],
        ],

        // NÃO declarar 'session' aqui (usamos a sessão definida em common)
        // mantendo a sessão em common garantimos partilha.

        // logging padrão
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
                // Rotas REST (exemplos)
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
                // adicione outras rules conforme necessário
            ],
        ],
    ],
    'params' => $params,
];
