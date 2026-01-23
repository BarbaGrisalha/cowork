<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

// Models corretos
use common\models\Rooms;
use common\models\Reservation;
use common\models\Customer;
use common\models\Economato;
use common\models\RoomItems;

class DashboardController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],  // Apenas usuários logados
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $userId = Yii::$app->user->id;

        // Busca o customer do usuário logado
        $customer = Customer::findOne(['user_id' => $userId]);

        if (!$customer) {
            Yii::$app->session->setFlash('error', 'Perfil de cliente não encontrado.');
            return $this->redirect(['site/index']);
        }

        // 1. Salas disponíveis (ativas)
        $locations = Rooms::find()
            ->where(['status' => 'ativa'])
            ->orderBy('nome_sala')
            ->all();

        // 2. Próximas reservas do usuário (futuras, não canceladas)
        $userReservations = Reservation::find()
            ->where(['customer_id' => $customer->id])
            ->andWhere(['>=', 'hora_inicio_agendada', date('Y-m-d H:i:s')])
            ->andWhere(['!=', 'status', 'cancelada'])
            ->with('room')
            ->orderBy(['hora_inicio_agendada' => SORT_ASC])
            ->all();

        // 3. Itens do bar / economato
        $barItems = Economato::find()
            ->orderBy('nome_item')
            ->all();

        // 4. Equipamentos extras
        $equipment = RoomItems::find()
            ->orderBy('nome_item')
            ->all();

        return $this->render('index', [
            'locations'        => $locations,
            'barItems'         => $barItems,
            'equipment'        => $equipment,
            'userReservations' => $userReservations,
        ]);
    }
}
