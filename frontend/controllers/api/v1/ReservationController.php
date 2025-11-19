<?php

namespace frontend\controllers\api\v1;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\rest\ActiveController;
use frontend\models\Reservations;

class ReservationController extends ActiveController
{
    public $modelClass = 'frontend\models\Reservations';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // Força retorno em JSON
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
        return $behaviors;
    }

    /**
     * Retorna os horários disponíveis para um recurso em uma data
     * GET /api/v1/availability/{resource}/{date}
     */
    public function actionAvailability($resource, $date)
    {
        // Todos os slots possíveis (se não vier de uma tabela de configuração)
        $allSlots = [
            ['start' => '08:00:00', 'end' => '09:00:00'],
            ['start' => '09:00:00', 'end' => '10:00:00'],
            ['start' => '10:00:00', 'end' => '11:00:00'],
            ['start' => '11:00:00', 'end' => '12:00:00'],
            ['start' => '14:00:00', 'end' => '15:00:00'],
            ['start' => '15:00:00', 'end' => '16:00:00'],
            ['start' => '16:00:00', 'end' => '17:00:00'],
            ['start' => '17:00:00', 'end' => '18:00:00'],
            ['start' => '18:00:00', 'end' => '19:00:00'],
        ];

        // Busca reservas existentes
        // CORRIGIDO: Usa 'room_id' (coluna real) e o nome correto das colunas no WHERE
        $reserved = Reservations::find()
            ->where(['room_id' => $resource, 'data_reserva' => $date])
            ->all();

        $result = [];
        foreach ($allSlots as $slot) {
            $isAvailable = true;
            foreach ($reserved as $res) {
                // CORRIGIDO: Usa 'booking_start_time' e 'booking_end_time' (colunas reais do seu DB)
                if ($res->hora_inicio_agendada == $slot['start']) {
                    $isAvailable = false;
                    break;
                }
            }
            $result[] = [
                'start' => $slot['start'],
                'end' => $slot['end'],
                'display_time' => date('H:i', strtotime($slot['start'])) . ' - ' . date('H:i', strtotime($slot['end'])),
                'is_available' => $isAvailable,
            ];
        }

        return $result;
    }

    /**
     * Crie uma nova reserva.
     * POST /api/v1/reservation
     */
    public function actionCreate()
    {
        // 1. Cria uma nova instância do Model
        $model = new Reservations();

        // 2. Carrega os dados do corpo da requisição POST
        // O Yii já consegue ler o JSON do POST se estiver configurado (e está)
        //$model->load(Yii::$app->request->getBodyParams(), ''); alterei para setAttribute();
        //$model->setAttributes(Yii::$app->request->getBodyParams(), '');
        $model->setAttributes(Yii::$app->request->getBodyParams());

        // 3. Valida e salva o Model
        if ($model->save()) {
            // Se salvar, retorna o Model. O status HTTP será 201 Created por padrão.
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        // Se falhar, retorna os erros de validação. O status HTTP será 422 Unprocessable Entity.
        Yii::$app->response->statusCode = 422;
        return [
            'errors' => $model->errors,
        ];
    }
}
