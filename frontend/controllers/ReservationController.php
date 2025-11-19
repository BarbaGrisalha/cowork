<?php

namespace frontend\controllers;

use backend\tests\FunctionalTester;
use Yii;
use yii\rest\Controller;
use yii\web\Response;
use frontend\models\Reservations; // O modelo que você acabou de gerar com sucesso
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use frontend\models\Room;

use frontend\models\Customer;

class ReservationController extends Controller
{
    // Define o modelo principal para este Controller REST
    //public $modelClass = Reservations::class;

    /* public function init()
    {
        parent::init();
        // Garante que o output é sempre JSON
        Yii::$app->response->format = Response::FORMAT_JSON;
    }
*/
    // Desabilitar o comportamento padrão do REST para usarmos as nossas ações
    public function actions()
    {
        return [];
    }

    /**
     * Ação GET: Obtém eventos para o calendário.
     * EndPoint: GET api/reservations/calendar?start=YYYY-MM-DD&end=YYYY-MM-DD
     */
    public function actionCalendarEvents($start, $end)
    {
        if (empty($start) || empty($end)) {
            Yii::$app->response->statusCode = 400;
            return ['error' => 'Datas de início e fim são obrigatórias.'];
        }

        // Lógica de consulta para sobreposição de datas
        $query = Reservations::find()
            ->where(['<=', 'hora_inicio_agendada', $end])
            ->andWhere(['>=', 'hora_fim_agendada', $start])
            ->with(['room', 'customer']);

        $reservations = $query->all();

        return $reservations;
    }

    /**
     * Ação POST: Cria uma nova reserva. (Próximo Passo)
     */
    public function actionCreate($location_id = null)
    {
        $model = new Reservations();
        $room = null; // Inicializa a variável para a View

        // 1. RECEBE O room_id DA URL (GET)
        $roomId = Yii::$app->request->get('room_id');

        if ($roomId) {
            // 2. CARREGA O ROOM (Sala) E ATRIBUI AO MODELO
            $room = Room::findOne($roomId);
            $model->room_id = $roomId;
        }

        // --- 3. CORREÇÃO CRÍTICA DO DEV MASTER: USER ID -> CUSTOMER ID ---

        // Pega o ID do usuário logado (User::id)
        $userId = Yii::$app->user->identity->getId();

        // Busca o registro do Cliente (Customer) que corresponde a este User ID
        $customer = Customer::findOne(['user_id' => $userId]);

        if (!$customer) {
            // Se o registro de cliente não existe, não há como salvar a reserva.
            Yii::$app->session->setFlash('error', 'Erro de Mapeamento: Seu perfil de Cliente não foi encontrado na base de dados. Contate o suporte.');
            return $this->redirect(['/site/index']);
        }

        // Atribui o ID PRINCIPAL do Cliente (Customer::id) ao modelo de reserva
        $model->customer_id = $customer->id;

        // -------------------------------------------------------------------

        // 4. CARREGA E VALIDA O FORMULÁRIO (AGORA COM O CUSTOMER ID CORRETO)
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Reserva criada com sucesso! Prossiga para o pagamento.');
            // Redireciona para o Checkout FAKE
            return $this->redirect(['/payment/checkout', 'reservation_id' => $model->id]);
        }

        // Se a validação falhar (dados de hora/data inválidos, etc.), apenas renderiza.
        // O erro será mostrado na View, desde que ela use os widgets corretamente.

        // 5. RENDERIZA A VIEW, PASSANDO O MODEL E O ROOM
        return $this->render('create', [
            'model' => $model,
            'room' => $room, // ESTE É O OBJETO QUE A VIEW PRECISA!
        ]);
    }
    /**
     * Ação GET: Obtém horários disponíveis para uma sala específica num dia.
     * EndPoint: GET api/reservations/available-slots?date=YYYY-MM-DD&room_id=X
     *
     * @param string $date A data da consulta (YYYY-MM-DD)
     * @param int $room_id O ID da sala
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionAvailableSlots($date, $room_id)
    {
        if (empty($date) || empty($room_id)) {
            Yii::$app->response->statusCode = 400;
            return ['error' => 'Data e ID da sala são obrigatórios.'];
        }

        // 1. Definir os horários de funcionamento (Ex: 09:00 - 18:00)
        // Idealmente, isto viria da configuração da sala ou da aplicação
        $businessStart = new DateTime($date . ' 09:00:00');
        $businessEnd = new DateTime($date . ' 19:00:00');
        $interval = new DateInterval('PT1H'); // Slots de 1 hora

        // 2. Buscar reservas *existentes* para esta sala neste dia
        $startOfDay = $date . ' 00:00:00';
        $endOfDay = $date . ' 23:59:59';

        $bookedReservations = Reservations::find()
            ->where(['room_id' => $room_id])
            ->andWhere(['<=', 'hora_inicio_agendada', $endOfDay])
            ->andWhere(['>=', 'hora_fim_agendada', $startOfDay])
            ->all();

        // 3. Gerar todos os slots possíveis e verificar a disponibilidade
        $allSlots = new DatePeriod($businessStart, $interval, $businessEnd);
        $availableSlots = [];

        foreach ($allSlots as $slotStart) {
            $slotEnd = (clone $slotStart)->add($interval);
            $isAvailable = true;

            // Verificar conflito com reservas existentes
            foreach ($bookedReservations as $booking) {
                $bookingStart = new DateTime($booking->hora_inicio_agendada);
                $bookingEnd = new DateTime($booking->hora_fim_agendada);

                // Lógica de sobreposição (overlap):
                // O slot começa antes que a reserva termine E o slot termina depois que a reserva começa
                if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                    $isAvailable = false;
                    break; // Conflito encontrado, não precisa verificar mais
                }
            }

            $availableSlots[] = [
                'start' => $slotStart->format('Y-m-d H:i:s'),
                'end'   => $slotEnd->format('Y-m-d H:i:s'),
                'label' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'), // Para o frontend
                'available' => $isAvailable
            ];
        }

        return $availableSlots;
    }

    public function actionIndex()
    {
        // 1. O CHECK CRÍTICO: Buscar o ID do usuário LOGADO
        // O Yii2 Advanced Template tem o objeto user
        $cliente_id = Yii::$app->user->identity->id;

        if (!$cliente_id) {
            // Se a paciência não for suficiente para tratar o erro, mande um 401
            throw new \yii\web\UnauthorizedHttpException('Acesso negado. Você precisa se identificar.');
        }

        // 2. BUSCA NO MODEL: Agora sim, buscando apenas as dele
        $reservas = Reserva::find()
            ->select(['data_hora_inicio', 'data_hora_fim'])
            ->where(['cliente_id' => $cliente_id]) // FILTRAR PELO ID DO USUÁRIO LOGADO
            ->andWhere(['>=', 'data_hora_fim', date('Y-m-d H:i:s')])
            ->orderBy('data_hora_inicio ASC')
            ->asArray()
            ->all();

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'status' => 'success',
            'data' => $reservas
        ];
    }

    public function behaviors(): array
    {
        return [
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'cancel' => ['POST'],
                ],
            ],

        ];
    }

    public function actionCancel($id)
    {
        //encontrar a reserva pelo id
        $model = Reservations::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('A reserva solicitada não existe.');
        }
        //Vê se o logado tem ou não permissão para cancelar - segurança
        if ($model->customer_id !== Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('Você não tem permissão para cancelar esta reserva');
        }
        // Lógica de Cancelamento:
        $model->status = 'cancelada';

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'A reserva foi cancelada com sucesso.');
        } else {
            Yii::$app->session->setFlash('error', 'Não foi possível cancelar a reserva.');
        }

        // Redireciona de volta para o Dashboard
        return $this->redirect(['/dashboard/index']);
    }
}
