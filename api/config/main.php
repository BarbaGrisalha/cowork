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

    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            'allowedIPs' => ['127.0.0.1', '::1', '*'],
        ],
    ],

    'controllerNamespace' => 'api\controllers',

    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'enableCsrfValidation' => false,
            'cookieValidationKey' => 'chave_secreta_para_a_api_aqui',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
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
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
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
                    'levels' => ['error', 'warning'],
                    'logFile' => '@api/runtime/logs/app.log',
                ],
            ],
        ],

        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'yii\base\BaseObject',  // Classe dummy que NÃO existe, mas não crasha o init() – truque para desativar validação
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
            'idParam' => '__api_user_id',  // Nome diferente para não conflitar
        ],

        // RateLimiter desativado corretamente (não boolean, mas configuração vazia)
        'rateLimiter' => [
            'class' => 'yii\filters\RateLimiter',
            'enabled' => false,  // Desativa o filter
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
