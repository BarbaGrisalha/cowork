<?php

namespace api\controllers;

use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBearerAuth;

class ReservationController extends ActiveController
{

    public $modelClass = 'common\models\Reservation';

    public function actions()
    {
        $actions = parent::actions();
        //unset($actions['index']);
        //unset($actions['view']);
        //unset($actions['create']);
        //unset($actions['update']);
        //unset($actions['delete']);
        $actions['update']['class'] = 'yii\rest\UpdateAction';
        $actions['update']['modelClass'] = $this->modelClass;
        $actions['update']['checkAccess'] = [$this, 'checkAccess'];
        $actions['update']['findModel'] = [$this, 'findModel'];
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
     * GET /reservation/availability/{tipo}/{data}
     * Ex: /reservation/availability/sala/2026-01-15
     */
    public function actionAvailability($resourceType, $date)
    {
        // Tipos válidos (baseado na tua ideia: sala, escritorio, mesa)
        $allowedTypes = ['sala', 'escritorio', 'mesa'];
        if (!in_array($resourceType, $allowedTypes)) {
            throw new NotFoundHttpException('Tipo de recurso inválido.');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new NotFoundHttpException('Formato de data inválido.');
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

        // Query real: reservas no dia para tipo (ajusta fields da tua BD)
        $booked = \common\models\Reservation::find()
            ->select('hora_inicio') // ou o field de slot/hora
            ->where(['date' => $date]) // ajusta field 'date' ou 'data_reserva'
            ->andWhere(['like', 'tipo_recurso', $resourceType]) // ou join com Rooms se tiver tipo
            ->column(); // retorna array de horas ocupadas

        $availableSlots = array_diff($allSlots, $booked);

        return [
            'success' => true,
            'date' => $date,
            'resourceType' => $resourceType,
            'availableSlots' => array_values($availableSlots),
            'bookedSlots' => $booked,
            'totalAvailable' => count($availableSlots),
        ];
    }
    public function actionMy()
    {
        $userId = Yii::$app->user->id;

        return \common\models\Reservation::find()
            ->where(['user_id' => $userId])
            ->all();
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Usa scenario update para permitir partial
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            return $model;
        }

        Yii::$app->response->statusCode = 422;
        return ['errors' => $model->errors];
    }
    /**
     * Encontra o modelo pela chave primária.
     * Se o modelo não for encontrado, lança 404 HTTP exception.
     * @param string $id ID do modelo
     * @return Reservation o modelo carregado
     * @throws NotFoundHttpException se o modelo não for encontrado
     */
    protected function findModel($id)
    {
        if (($model = Reservation::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('A reserva solicitada não existe.');
    }
}
