<?php

namespace api\controllers;

use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class ReservationController extends ActiveController
{
    /*
    public $modelClass = 'common\models\Reservation';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Remove qualquer autenticação herdada (deixa disponibilidade pública)
        unset($behaviors['authenticator']);

        return $behaviors;
    }

    // Desabilita ações que não queremos expor ainda
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function actionAvailability($resourceType, $date)
    {
        $allowedTypes = ['sala', 'escritorio', 'mesa'];
        if (!in_array($resourceType, $allowedTypes)) {
            throw new NotFoundHttpException('Tipo de recurso inválido.');
        }

        // Horários disponíveis no cowork (mock por enquanto)
        $allSlots = ['08:00-10:00', '10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00'];
        $bookedSlots = [];

        // Simula algumas reservas (só pra ver diferença)
        if ($date === '2026-01-15') {
            if ($resourceType === 'sala') {
                $bookedSlots = ['10:00-12:00', '14:00-16:00'];
            } elseif ($resourceType === 'mesa') {
                $bookedSlots = ['08:00-10:00'];
            }
        }

        $availableSlots = array_values(array_diff($allSlots, $bookedSlots));

        return [
            'success' => true,
            'date' => $date,
            'resourceType' => $resourceType,
            'availableSlots' => $availableSlots,
            'bookedSlots' => $bookedSlots,
            'totalAvailable' => count($availableSlots),
        ];
    }
        */

    public $modelClass = 'common\models\Reservation';

    /**
     * Desabilita todas as ações padrão do ActiveController
     * Assim não tenta aceder ao banco nas rotas index/view/create/update/delete
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * Remove qualquer autenticação (deixa a disponibilidade pública)
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        return $behaviors;
    }

    /**
     * GET /reservation/availability/{tipo}/{data}
     * Ex: /reservation/availability/sala/2026-01-15
     */
    public function actionAvailability($resourceType, $date)
    {
        // Tipos válidos (baseado na tua ideia: sala, escritorio, mesa)
        $allowedTypes = ['sala', 'escritorio', 'mesa'];
        if (!in_array($resourceType, $allowedTypes)) {
            throw new NotFoundHttpException('Tipo de recurso inválido. Use: sala, escritorio ou mesa.');
        }

        // Validação simples da data
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new NotFoundHttpException('Formato de data inválido. Use YYYY-MM-DD.');
        }

        // Horários padrão do cowork (podes mudar conforme o teu horário de funcionamento)
        $allSlots = [
            '08:00-09:00',
            '09:00-10:00',
            '10:00-11:00',
            '11:00-12:00',
            '12:00-13:00',
            '14:00-15:00',
            '15:00-16:00',
            '16:00-17:00',
            '17:00-18:00',
            '18:00-19:00',
        ];

        // Simulação de alguns horários ocupados (só para testar)
        // Depois vamos substituir isto pela consulta real à tabela reservations
        $bookedSlots = [];
        if ($date === '2026-01-15') {
            if ($resourceType === 'sala') {
                $bookedSlots = ['10:00-11:00', '14:00-15:00', '16:00-17:00'];
            } elseif ($resourceType === 'mesa') {
                $bookedSlots = ['09:00-10:00', '17:00-18:00'];
            }
        }

        $availableSlots = array_values(array_diff($allSlots, $bookedSlots));

        return [
            'success' => true,
            'date' => $date,
            'resourceType' => $resourceType,
            'totalSlots' => count($allSlots),
            'availableSlots' => $availableSlots,
            'bookedSlots' => $bookedSlots,
            'totalAvailable' => count($availableSlots),
            'message' => 'Disponibilidade carregada (dados simulados – em breve consulta real ao banco)',
        ];
    }
}
