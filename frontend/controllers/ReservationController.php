<?php

namespace frontend\controllers;

use backend\tests\FunctionalTester;
use Yii;
use yii\rest\Controller;
use yii\web\Response;
use frontend\models\Reservations; // O modelo que você acabou de gerar com sucesso
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use common\models\Rooms;

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
        $room = null;

        // --- RECEBE room_id ---
        $roomId = Yii::$app->request->get('room_id');
        if ($roomId) {
            $room = Rooms::findOne($roomId);
            $model->room_id = $roomId;
        }

        // --- CUSTOMER LOGADO ---
        $userId = Yii::$app->user->identity->getId();
        $customer = Customer::findOne(['user_id' => $userId]);

        if (!$customer) {
            Yii::$app->session->setFlash('error', 'Seu perfil de Cliente não foi encontrado.');
            return $this->redirect(['/site/index']);
        }

        $model->customer_id = $customer->id;

        // --- PARÂMETROS DO GET PARA DIFERENTES PLANOS ---
        $type   = Yii::$app->request->get('type');      // hourly | daily | monthly
        $date   = Yii::$app->request->get('date');      // daily
        $inicio = Yii::$app->request->get('inicio');    // monthly
        $fim    = Yii::$app->request->get('fim');       // monthly

        // ==========================================
        // 🔵 1. FLUXO DAILY
        // ==========================================
        if ($type === 'daily' && $date) {
            $model->data_reserva = $date;
            $model->hora_inicio_agendada = $date . " 09:00:00";
            $model->hora_fim_agendada    = $date . " 19:00:00";
            $model->total_estimado = 32.00; // valor fixo
        }

        // ==========================================
        // 🔵 2. FLUXO MONTHLY
        // ==========================================
        if ($type === 'monthly' && $inicio && $fim) {
            $model->data_reserva = $inicio;
            $model->hora_inicio_agendada = $inicio . " 00:00:00";
            $model->hora_fim_agendada    = $fim . " 23:59:59";
            $model->total_estimado = 225.00; // valor fixo
        }

        // ==========================================
        // 🔵 3. FLUXO HOURLY (teu fluxo já existente)
        // ==========================================
        // Nesse caso, o form de hours vai preencher tudo via POST normalmente


        // ==========================================
        // 🔵 SALVA A RESERVA
        // ==========================================
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Reserva criada com sucesso! Prossiga para o pagamento.');
            return $this->redirect(['/payment/checkout', 'reservation_id' => $model->id]);
        }

        // Renderiza view normal se não enviou POST
        return $this->render('create', [
            'model' => $model,
            'room'  => $room,
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
    /**
     * Endpoint para receber o webhook de pagamento aprovado.
     * Deve ser acessado via POST pelo gateway de pagamento.
     * @param int $reservationId O ID da reserva.
     */
    public function actionConfirmReservationFromWebhook($reservationId)
    {
        // ... (Coloque aqui TODA a lógica de Active Record que te passei antes) ...

        $reservation = Reservations::findOne($reservationId);

        if ($reservation === null) {
            // Lógica de erro...
            throw new NotFoundHttpException("Reserva não encontrada.");
        }

        // As 3 Linhas que funcionam:
        $reservation->status = 'Confirmado';
        if ($reservation->save()) {
            // Sucesso e log...
            return "Reserva #{$reservationId} CONFIRMADA.";
        }
    }

    public function actionSelectHourly()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->setReturnUrl(['/reservation/select-hourly']);
            return $this->redirect(['/site/login']);
        }

        return $this->redirect(['/dashboard/index']);
    }

    public function actionCheckoutDaily()
    {
        $date    = trim(Yii::$app->request->post('data_inicio'));
        $room_id = Yii::$app->request->post('room_id');

        // VALIDAÇÃO QUE NUNCA FALHA
        if (empty($date) || empty($room_id) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Yii::$app->session->setFlash('error', 'Escolhe uma data válida e uma sala!');
            return $this->redirect(['dashboard/index']);
        }

        // Converte pra formato seguro
        $date = date('Y-m-d', strtotime($date));
        if ($date < date('Y-m-d')) {
            Yii::$app->session->setFlash('error', 'Não pode reservar no passado!');
            return $this->redirect(['dashboard/index']);
        }
        // Verifica se já tem reserva nesse dia (simples e bruto)
        $jaReservado = Reservations::find()
            ->where(['room_id' => $room_id])
            ->andWhere(['<=', 'data_inicio', $date])
            ->andWhere(['>=', 'data_fim', $date])
            ->andWhere(['<>', 'status', 'cancelado'])
            ->exists();

        if ($jaReservado) {
            Yii::$app->session->setFlash('error', 'Essa data já tá ocupada, escolhe outra!');
            return $this->redirect(['dashboard/index']);
        }

        // Pega o cliente
        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        if (!$customer) {
            Yii::$app->session->setFlash('error', 'Cadê seu perfil, mano?');
            return $this->redirect(['site/index']);
        }

        // CRIA A RESERVA NA MARRA (sem validação chata)
        $model = new Reservations();
        $model->room_id              = $room_id;
        $model->customer_id          = $customer->id;
        $model->data_reserva         = $date;
        $model->data_inicio          = $date;
        $model->data_fim             = $date;
        $model->hora_inicio_agendada = '09:00:00';
        $model->hora_fim_agendada    = '19:00:00';
        $model->total_estimado       = 32.00;
        $model->status               = 'Pendente';
        $model->periodo              = 'dia';

        // SALVA SEM VALIDAR (a validação que tá te matando)
        if ($model->save(false)) {  // ← O "false" aqui é o SEGREDO DA VIDA
            return $this->redirect(['payment/checkout', 'reservation_id' => $model->id]);
        }

        // Se mesmo assim der erro (quase impossível)
        Yii::$app->session->setFlash('error', 'Deu ruim mesmo salvando na marra: ' . implode(' | ', $model->getFirstErrors()));
        return $this->redirect(['dashboard/index']);
    }
    public function actionSelectMonthly()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->setReturnUrl(['/reservation/select-monthly']);
            return $this->redirect(['/site/login']);
        }

        return $this->render('select-monthly'); // criar esta view
    }
    /**Busca da seleção para receber a seleção somente diária
     * aqui vamos buscar isso -.
     * 
     */
    public function actionSelectDaily($room_id = null)
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->setReturnUrl(['/reservation/select-daily', 'room_id' => $room_id]);
            return $this->redirect(['/site/login']); //linha 355
        }

        if (!$room_id || !is_numeric($room_id)) {
            throw new NotFoundHttpException('Sala não informada.'); //linha 359
        }

        $room = \common\models\Rooms::findOne($room_id);
        if (!$room || $room->status !== 'ativa') {
            throw new NotFoundHttpException('Sala indisponível.'); //linha 364
        }

        // Pega todas as datas já reservadas desta sala
        $reserved = \frontend\models\Reservations::find()
            ->select(['data_reserva'])
            ->where(['room_id' => $room_id])
            ->andWhere(['>=', 'data_reserva', date('Y-m-d')])
            ->column(); //

        $reservedDates = array_map(function ($d) {
            return date('Y-m-d', strtotime($d));
        }, $reserved);

        return $this->render('select-daily', [
            'room' => $room,
            'reservedDates' => $reservedDates
        ]);
    }
    public function actionCheckoutMonthly()
    {
        $data_inicio = Yii::$app->request->post('data_inicio');
        $room_id     = Yii::$app->request->post('room_id');

        if (!$data_inicio || !$room_id) {
            Yii::$app->session->setFlash('error', 'Preenche tudo direito!');
            return $this->redirect(['dashboard/index']);
        }

        $inicio = date('Y-m-01', strtotime($data_inicio));
        $fim    = date('Y-m-t', strtotime($inicio));

        // Verifica se qualquer dia do mês tá ocupado
        $jaReservado = Reservations::find()
            ->where(['room_id' => $room_id])
            ->andWhere(['<', 'data_fim', $fim])
            ->andWhere(['>', 'data_inicio', $inicio])
            ->andWhere(['<>', 'status', 'cancelado'])
            ->exists();

        if ($jaReservado) {
            Yii::$app->session->setFlash('error', 'Esse mês já tá reservado!');
            return $this->redirect(['dashboard/index']);
        }

        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        if (!$customer) return $this->redirect(['site/index']);

        $model = new Reservations();
        $model->room_id              = $room_id;
        $model->customer_id          = $customer->id;
        $model->data_reserva         = $inicio;
        $model->data_inicio          = $inicio;
        $model->data_fim             = $fim;
        $model->hora_inicio_agendada = '09:00:00';
        $model->hora_fim_agendada    = '19:00:00';
        $model->total_estimado       = 800.00;
        $model->status               = 'Pendente';
        $model->periodo              = 'mes';

        if ($model->save(false)) {  // ← SEM VALIDAÇÃO, SÓ SALVA
            return $this->redirect(['payment/checkout', 'reservation_id' => $model->id]);
        }

        Yii::$app->session->setFlash('error', 'Falha total ao reservar o mês.');
        return $this->redirect(['dashboard/index']);
    }
}
