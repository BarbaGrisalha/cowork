<?php
// Carrega parâmetros globais e locais
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php', // Crie este arquivo, se não existir
    require __DIR__ . '/params-local.php' // Crie este arquivo, se não existir
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    // 1. INFORMA ONDE ESTÃO OS CONTROLLERS DA API
    'controllerNamespace' => 'api\controllers',
    'components' => [
        'request' => [
            // Permite ao Yii ler JSON no corpo de requisições POST/PUT
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            // Desativa CSRF Validation para API RESTful
            'enableCsrfValidation' => false,
            'cookieValidationKey' => 'chave_secreta_para_a_api_aqui', // Altere por uma chave real
        ],
        // 2. CONFIGURA O GERENCIADOR DE URLS (CRÍTICO PARA O ROTEAMENTO)
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Regra RESTful para o nosso BookingController
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'reservation', // Mapeia para api\controllers\BookingController
                    'extraPatterns' => [
                        // Mapeia: GET /booking/availability/TIPO/DATA -> actionAvailability
                        //'GET reservation/<resourceType>/<date:\d{4}-\d{2}-\d{2}>' => 'reservation',
                    ],
                    'pluralize' => false,
                ],
            ],
        ],
        // Opcional, mas limpo: Garante que a resposta padrão é JSON
        'response' => [
            'format' => \yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        // ... (Outros componentes como log, user, etc.)
    ],
    'params' => $params,
];
