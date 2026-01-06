<?php

use yii\caching\FileCache;
use App\Payment\Gateway\PaymentGatewayInterface;
use App\Payment\Gateway\MockPaymentGateway;
use App\Reservation\Repository\ReservationRepository;
use App\Payment\Repository\PaymentRepository;
use App\Payment\Service\PaymentCreationService;

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',

        // 🚨 NOVO: Define o alias @App para apontar para a pasta comum (common/)
        '@App' => dirname(__DIR__),
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => FileCache::class,
        ],
        'paymentGatewayService' => [
            'class' => 'common\components\PaymentGatewayService',
            'secretKey' => getenv('GATEWAY_SECRET_KEY'), // Use variáveis de ambiente!
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // uncomment if you want to cache RBAC items hierarchy
            // 'cache' => 'cache',
        ],
    ],

    // =======================================================
    // 🚀 NOVO BLOCO: DI CONTAINER (Injeção de Dependência)
    // =======================================================
    'container' => [
        'definitions' => [
            // 1. Definição da Interface (Contrato)
            // Onde o Yii2 vir PaymentGatewayInterface, ele deve usar o Mock
            PaymentGatewayInterface::class => [
                'class' => MockPaymentGateway::class,
            ],

            // 2. Definição dos Repositórios
            ReservationRepository::class => [
                'class' => ReservationRepository::class,
            ],
            PaymentRepository::class => [
                'class' => PaymentRepository::class,
            ],

            // 3. O Service (Principal)
            // O Yii2 sabe que ele precisa dos itens 1 e 2, e os injeta automaticamente.
            PaymentCreationService::class => [
                'class' => PaymentCreationService::class,
            ],
        ],
    ],
    'components' => [
        'i18n' => [
            'translations' => [
                'yii/bootstrap5' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@vendor/yiisoft/yii2-bootstrap5/src/messages',
                ],
            ],
        ],
    ],
];
