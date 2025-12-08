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
            'loginUrl' => ['site/login'],
        ],
        'view' => [],
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
            'rules' => [
                // Rota principal para o agendamento
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/booking'], // Assumindo o módulo 'v1'
                    'extraPatterns' => [
                        'GET availability/<resourceType>/<date:\d{4}-\d{2}-\d{2}>' => 'availability',
                    ],
                ],


                'relatorio/clientes-mes-atual/<mes:\d{4}-\d{2}>' => 'relatorio/clientes-mes-atual',
                'relatorio/clientes-futuros' => 'relatorio/clientes-proximos-meses',
                'relatorio/salas-ranking/<mes:\d{4}-\d{2}>' => 'relatorio/salas-mais-alugadas',
                'relatorio/reservas-salas/<mes:\d{4}-\d{2}>' => 'relatorio/reservas-por-sala',

            ],
        ],

    ],
    'params' => $params,
];
