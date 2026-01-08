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

    // MÓDULOS DEVEM VIR AQUI (no nível principal)
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
            'enableStrictParsing' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'reservation', // vai mapear para ReservationController.php
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET availability/{resourceType}/{date:\d{4}-\d{2}-\d{2}}' => 'availability',
                    ],
                ],
            ],
        ],

        'response' => [
            'format' => \yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
    ],

    'params' => $params,
];
