<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'name' => 'Cowork IPLeiria',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'modules' => [
        'api' => [
            'class' => 'frontend\modules\api\Module',
            'version' => 'v1', // opcional, mas útil se quiser versionar
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            // ADICIONAR ESTE BLOCO: CONFIGURA O JSON PARSER
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],

        ],
        // ADICIONE ESTE BLOCO: CONFIGURAÇÃO DO FORMATADOR
        'formatter' => [
            // Define o local e a moeda. Ajuste conforme sua região.
            'locale' => 'pt-PT', // Define o idioma e a região
            'currencyCode' => 'EUR', // Define a moeda padrão (ex: Euro)
            'defaultTimeZone' => 'Europe/Lisbon', // Ou 'America/Sao_Paulo'
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity-frontend',
                'httpOnly' => true,
            ],
            'loginUrl' => ['auth/portal'],
        ],
        'session' => [
            // Nome do cookie de sessão para o frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info', 'trace'],  // ← adicione 'info' e 'trace'
                    'except' => ['yii\web\HttpException:*'],
                    'logFile' => '@runtime/logs/app.log',
                    'logVars' => ['_GET', '_POST', '_FILES'],  // útil para ver POST
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
                // ROTA ESPECÍFICA PARA LOGIN NA API
                'POST api/login' => 'api/login/login', // módulo 'api' / controller 'login' / action 'login'
                // ROTAS DA API (v1)
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'api/reservation',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET availability/{room_id}/{date}' => 'availability',
                        'GET events' => 'events',
                    ],
                ],

                // ROTAS NORMAIS DO SITE (as mais importantes primeiro)
                'reservation/escolher' => 'reservation/escolher',
                'reservation/create/<room_id:\d+>' => 'reservation/create',
                'reservation/create' => 'reservation/create',
                'reservation/index' => 'reservation/index',
                'reservation/historic' => 'reservation/historic',
                'invoice' => 'invoice/index',
                'invoice/view/<id:\d+>' => 'invoice/view',
                'invoice/pdf/<id:\d+>' => 'invoice/pdf',

                // ROTA PADRÃO
                '' => 'site/index',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
            ],
        ],
        // -------------------------------
        // URL Manager: Rotas Bonitas + API
        // -------------------------------

        // 'urlManager' => [
        //     'enablePrettyUrl' => true,
        //     'showScriptName' => false,
        //     'rules' => [

        //         [
        //             'class' => 'yii\rest\UrlRule',
        //             'controller' => 'api/reservation',
        //             'pluralize' => false,
        //             // AQUI ESTÁ O REMÉDIO: DEFINIÇÃO DE TOKENS
        //             'tokens' => [
        //                 // {resource} deve ser numérico (assumindo que é um ID)
        //                 '{resource}' => '<resource:\d+>',
        //                 // {date} deve aceitar o formato YYYY-MM-DD (dígitos e hífens)
        //                 // O padrão é flexível o suficiente para lidar com a data
        //                 '{date}' => '<date:\d{4}-\d{2}-\d{2}>',
        //             ],
        //             'patterns' => [
        //                 // Esta é a sua regra. Ela se beneficia dos tokens definidos acima.
        //                 'GET availability/{resource}/{date}' => 'availability',

        //                 // CRUD Genéricas (sem ID)
        //                 'GET' => 'index',
        //                 'POST' => 'create',

        //                 // CRUD Genéricas (com ID)
        //                 'GET <id>' => 'view',
        //                 'PUT <id>' => 'update',
        //                 'DELETE <id>' => 'delete',
        //             ],
        //             'extraPatterns' => [],
        //         ],

        //         // 2. Rotas padrão do site (Estas vêm depois)
        //         '' => 'site/index',
        //         '<controller:\w+>/<action:\w+>/' => '<controller>/<action>',
        //     ],
        // ],
    ],
    'params' => $params,
];
