<?php

namespace api\controllers;

use Yii;
use yii\rest\Controller;

class FaturaController extends Controller
{
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
     * GET /faturas/my?customer_id=1
     */
    public function actionMy()
    {
        $customerId = Yii::$app->request->get('customer_id');

        if ($customerId === null) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'customer_id obrigatório'];
        }

        $customerId = (int)$customerId;

        // Busca reservas pagas/confirmadas (ou crie tabela específica de faturas se quiser)
        $faturas = \common\models\Reservation::find()
            ->where(['customer_id' => $customerId, 'status' => ['pago', 'confirmada']])
            ->orderBy(['hora_inicio_agendada' => SORT_DESC])
            ->all();

        return [
            'success' => true,
            'faturas' => $faturas
        ];
    }
}
