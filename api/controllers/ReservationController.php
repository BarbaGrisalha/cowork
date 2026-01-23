<?php

namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class ReservationController extends ActiveController
{
    public $modelClass = 'common\models\Reservation';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);  // Desabilita create padrão (já temos custom)
        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['cors'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        return $behaviors;
    }

    /**
     * POST /reservation - Cria nova reserva
     */
    public function actionCreate()
    {
        $model = new $this->modelClass();

        $post = Yii::$app->request->post();

        // Log para debug (veja no console PHP)
        Yii::info('POST recebido em actionCreate: ' . json_encode($post), __METHOD__);

        if ($model->load($post, '') && $model->validate()) {
            try {
                if ($model->save()) {
                    // Recarrega o model para pegar reservation_code gerado no afterSave
                    $model->refresh();

                    Yii::$app->response->statusCode = 201;
                    return [
                        'success' => true,
                        'id' => $model->id,
                        'reservation_code' => $model->reservation_code ?? 'N/A'
                    ];
                }
            } catch (\yii\db\IntegrityException $e) {
                Yii::error('Erro de integridade: ' . $e->getMessage(), __METHOD__);
                if ($e->errorInfo[1] === 1062) {
                    Yii::$app->response->statusCode = 409;
                    return [
                        'success' => false,
                        'message' => 'Este horário já está reservado para a sala selecionada.'
                    ];
                }
                throw $e;
            }

            Yii::$app->response->statusCode = 422;
            return [
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $model->getErrors()
            ];
        }

        Yii::$app->response->statusCode = 400;
        return [
            'success' => false,
            'message' => 'Dados não recebidos ou inválidos',
            'errors' => $model->getErrors()
        ];
    }

    /**
     * GET /reservation/availability?date=2026-01-15&room_id=1
     */
    public function actionAvailability()
    {
        $date = Yii::$app->request->get('date');
        $roomId = Yii::$app->request->get('room_id');

        if (!$date || !$roomId) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Parâmetros date e room_id obrigatórios'];
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Formato de data inválido (YYYY-MM-DD)'];
        }

        // Horários padrão (ajuste conforme necessidade)
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
            '18:00-19:00'
        ];

        // Busca reservas no dia e sala específica
        $booked = \common\models\Reservation::find()
            ->select(['hora_inicio_agendada', 'hora_fim_agendada'])
            ->where(['data_reserva' => $date])
            ->andWhere(['room_id' => $roomId])
            ->all();

        $availableSlots = $allSlots;

        foreach ($booked as $res) {
            $inicio = substr($res->hora_inicio_agendada, 11, 5);
            $fim = substr($res->hora_fim_agendada, 11, 5);
            $slot = $inicio . '-' . $fim;
            if (($key = array_search($slot, $availableSlots)) !== false) {
                unset($availableSlots[$key]);
            }
        }

        return [
            'success' => true,
            'date' => $date,
            'room_id' => $roomId,
            'availableSlots' => array_values($availableSlots),
            'bookedSlots' => $booked,
            'totalAvailable' => count($availableSlots)
        ];
    }

    /**
     * GET /reservation/my?customer_id=23
     */
    public function actionMy()
    {
        $customerId = Yii::$app->request->get('customer_id');

        if ($customerId === null) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Parâmetro customer_id obrigatório'];
        }

        $customerId = (int)$customerId;

        $reservations = \common\models\Reservation::find()
            ->where(['customer_id' => $customerId])
            ->andWhere(['!=', 'status', 'cancelada'])
            ->orderBy(['hora_inicio_agendada' => SORT_DESC])
            ->all();

        return [
            'success' => true,
            'reservations' => $reservations
        ];
    }

    /**
     * PUT /reservation/{id} - Atualiza reserva
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $post = Yii::$app->request->post();

        // Log para debug
        Yii::info('PUT recebido para reserva ' . $id . ': ' . json_encode($post), __METHOD__);

        // Carrega apenas campos permitidos
        $allowedFields = ['status', 'data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada', 'tipo_reserva'];
        $data = [];
        foreach ($allowedFields as $field) {
            if (isset($post[$field])) {
                $data[$field] = $post[$field];
            }
        }

        if ($model->load($data, '') && $model->validate($allowedFields)) {
            if ($model->save()) {
                $model->refresh();
                return [
                    'success' => true,
                    'message' => 'Reserva atualizada',
                    'reserva' => $model
                ];
            }
        }

        Yii::$app->response->statusCode = 422;
        return ['success' => false, 'errors' => $model->getErrors()];
    }

    protected function findModel($id)
    {
        if (($model = \common\models\Reservation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Reserva não encontrada.');
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->response->setStatusCode(204);
        return null;
    }
}
