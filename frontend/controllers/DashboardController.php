<?php

namespace frontend\controllers;

use app\models\Room;
use common\models\Economato;
use common\models\RoomItems;
use yii\filters\AccessControl;
use Yii; // Necessário para acessar o ID do usuário logado

// Assumindo que os Models estão em common ou frontend e se chamam Room e Reservation.
// Você pode precisar ajustar os 'use' statements.
use common\models\Rooms;
use frontend\models\Reservations;

class DashboardController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $userId = Yii::$app->user->id;
        // 1. Locais disponíveis (Salas, etc.)
        // Assumindo que a tabela é 'rooms' ou 'locations'
        // Se o Model Room for 'common\models\Room'
        $locations = Rooms::find()->all(); //no Room aparece isso Undefined type 'common\models\Room'.intelephense(P1009) No quick fixes available

        // 2. Reservas do usuário logado
        // Assumindo que o Model Reservations tem a coluna 'customer_id'
        $userReservations = Reservations::find()
            ->where(['customer_id' => $userId])
            ->with('room')
            ->orderBy(['data_reserva' => SORT_ASC, 'hora_inicio_agendada' => SORT_ASC])
            ->all();

        // 3. Itens de Bar e Equipamentos (Mocks temporários, ajuste com seus Models reais)
        // Se você tiver Models como Economato, use Economato::find()->where(['type' => 'bar'])->all();
        /*$barItems = [
            ['name' => 'Café Espresso', 'price' => 0.50],
            ['name' => 'Chá', 'price' => 0.60],
            ['name' => 'Água', 'price' => 1.00],
            ['name' => 'Snacks', 'price' => 1.50],
        ];*/
        $barItems = Economato::find()->all();
        /*
        $equipment = [
            ['name' => 'Projetor HD', 'price' => 10.00],
            ['name' => 'Webcam Profissional', 'price' => 5.00],
            ['name' => 'Microfone Profissional', 'price' => 5.00],
        ];*/
        $equipment = RoomItems::find()->all();
        // Passa todas as variáveis necessárias para a View
        return $this->render('index', [
            'locations' => $locations,
            'barItems' => $barItems,
            'equipment' => $equipment,
            'userReservations' => $userReservations,
        ]);
    }
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        // CORREÇÃO CRÍTICA: 'roles' deve ser um array de strings.
                        // O Yii estava lendo o array de roles como se fossem propriedades
                        'roles' => ['@'], // ['@'] significa: usuários autenticados
                        'actions' => ['index'], // Adicionamos 'actions' para maior clareza
                    ],
                ],
            ],
        ];
    }
}
