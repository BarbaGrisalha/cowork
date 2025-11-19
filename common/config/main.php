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
];
