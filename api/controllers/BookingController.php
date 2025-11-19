<?php

declare(strict_types=1);

// O namespace do seu módulo 'Api'
namespace api\controllers;

use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use Yii;
// Importando o Service que criamos e colocamos em /common/services/
use common\services\BookingService;

/**
 * Controller RESTful para gerenciar a disponibilidade e criação de reservas (FrontOffice).
 * Rota Exemplo: GET /api/v1/booking/availability/private_office/2025-10-25
 */
class BookingController extends Controller
{
    private BookingService $bookingService;

    // A Injeção de Dependência via construtor é o método mais limpo.
    public function __construct($id, $module, BookingService $bookingService, $config = [])
    {
        $this->bookingService = $bookingService;
        parent::__construct($id, $module, $config);
    }

    // Configurações do comportamento do Controller (muito importante para APIs)
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        // **1. Content Negotiator:** Força a resposta a ser JSON (padrão REST)
        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
            ],
        ];

        // **2. Autenticação/CORS:** Aqui iriam os filtros de autenticação (Bearer/JWT) e CORS
        /*
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
        ];
        */

        return $behaviors;
    }

    /**
     * [GET /api/v1/booking/availability/<resourceType>/<date>]
     * Retorna os horários disponíveis (a action que o frontend está chamando).
     * * @param string $resourceType 
     * @param string $date 
     * @return array
     */
    public function actionAvailability(string $resourceType, string $date): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new BadRequestHttpException('O formato da data deve ser YYYY-MM-DD.');
        }

        try {
            // Chamada ao Service Layer em common
            $slots = $this->bookingService->getDailySlots($resourceType, $date);

            // Retorna o array para serialização JSON
            return $slots;
        } catch (\Exception $e) {
            Yii::error("Falha ao buscar slots: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Erro ao processar a requisição de disponibilidade.');
        }
    }
}
