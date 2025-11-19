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
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],


        // -------------------------------
        // URL Manager: Rotas Bonitas + API
        // -------------------------------

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'api/reservation',
                    'pluralize' => false,
                    // AQUI ESTÁ O REMÉDIO: DEFINIÇÃO DE TOKENS
                    'tokens' => [
                        // {resource} deve ser numérico (assumindo que é um ID)
                        '{resource}' => '<resource:\d+>',
                        // {date} deve aceitar o formato YYYY-MM-DD (dígitos e hífens)
                        // O padrão é flexível o suficiente para lidar com a data
                        '{date}' => '<date:\d{4}-\d{2}-\d{2}>',
                    ],
                    'patterns' => [
                        // Esta é a sua regra. Ela se beneficia dos tokens definidos acima.
                        'GET availability/{resource}/{date}' => 'availability',

                        // CRUD Genéricas (sem ID)
                        'GET' => 'index',
                        'POST' => 'create',

                        // CRUD Genéricas (com ID)
                        'GET <id>' => 'view',
                        'PUT <id>' => 'update',
                        'DELETE <id>' => 'delete',
                    ],
                    'extraPatterns' => [],
                ],

                // 2. Rotas padrão do site (Estas vêm depois)
                '' => 'site/index',
                '<controller:\w+>/<action:\w+>/' => '<controller>/<action>',
            ],



            /* 'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // 1. ROTAS CUSTOMIZADAS (Usando a regra padrão para forçar o reconhecimento)
                // O formato é: 'url' => 'controller/action'
                'GET api/reservation/events' => 'api/reservation/events',
                'GET api/reservation/availability/<resource:\w+>/<date:\w+>' => 'api/reservation/availability',

                // 2. ROTAS REST PADRÃO (Usando o UrlRule, mas sem as customizações que estavam falhando)
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'api/reservation',
                    'pluralize' => false,
                    'except' => ['index', 'create', 'update', 'delete', 'view'], // Desativa tudo (ou deixe apenas 'view' se for mais simples)
                ],

                // 3. Rotas padrão do site
                '' => 'site/index',
                '<controller:\w+>/<action:\w+>/' => '<controller>/<action>',
            ],
            /*Alterado para teste
            'rules' => [
                // Rotas REST da API versão 1
                [
                    'class' => 'yii\rest\UrlRule',
                    // CORRIGIDO: O ID REAL do seu Controller é 'api/v1/reservation'
                    //'controller' => 'api/v1/reservation',
                    'controller' => 'api/reservation',
                    'pluralize' => false,
                    'patterns' => [
                        // 1. ROTAS COM PARÂMETROS DE CAMINHO NO TOPO (MAIS ESPECÍFICAS)
                        //'GET availability/{resource}/{date}' => 'availability',

                        // 2. ROTAS LITERAIS (ESPECÍFICAS, SEM PARÂMETROS)
                        'GET events' => 'events',

                        // 3. ROTAS DE RECURSOS (GENÉRICAS, SEM ID)
                       //200 'GET' => 'index',
                        //'POST' => 'create',

                        // 4. ROTAS COM ID (GENÉRICAS, NO FINAL)
                        'GET <id>' => 'view',
                        //'PUT <id>' => 'update',
                        //'DELETE <id>' => 'delete',

                    ],
                    'extraPatterns' => [
                        // CORRIGIDO: O nome da action é 'availability'
                        'GET availability/{resource}/{date}' => 'availability'
                    ],
                    'tokens' => [],
                    'pluralize' => false,
                ],

                // Rotas padrão do site
                '' => 'site/index',
                '<controller:\w+>/<action:\w+>/' => '<controller>/<action>',
            ],*/
        ],
    ],
    'params' => $params,
];
