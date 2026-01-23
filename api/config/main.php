<?php

// Carrega parâmetros globais e locais
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'debug'],

    'controllerNamespace' => 'api\controllers',

    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            'allowedIPs' => ['127.0.0.1', '::1', '*'],
        ],
    ],

    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser',
            ],
            'enableCsrfValidation' => false,
            'cookieValidationKey' => 'chave_secreta_para_a_api_aqui',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                'auth/login' => 'auth/login',

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'auth',
                    'pluralize' => false,
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'reservation',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET availability/{resourceType}/{date:\d{4}-\d{2}-\d{2}}' => 'availability',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'rooms',
                    'pluralize' => false,
                ],

                'faturas/my' => 'fatura/my',

                // Customers - rota customizada para update do próprio perfil
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'customers',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'PUT update' => 'update',  // PUT /customers/update chama actionUpdate
                        'GET my' => 'my',          // opcional: GET /customers/my para ver perfil
                    ],
                ],

                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                'PUT customers/update-profile' => 'customers/update-profile',
            ],
        ],

        'response' => [
            'format' => \yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@api/runtime/logs/app.log',
                ],
            ],
        ],

        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
            'idParam' => '__api_user_id',
        ],

        'rateLimiter' => [
            'class' => 'yii\filters\RateLimiter',
            'enabled' => false,
        ],
    ],

    'as cors' => [
        'class' => \yii\filters\Cors::class,
        'cors' => [
            'Origin' => ['*'],
            'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD'],
            'Access-Control-Request-Headers' => ['*'],
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Max-Age' => 3600,
            'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page', 'X-Pagination-Page-Count'],
        ],
    ],

    'params' => $params,
];
