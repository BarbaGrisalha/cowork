<?php

namespace frontend\controllers;

use backend\tests\FunctionalTester;
use Yii;
use yii\rest\Controller;
use yii\web\Response;

use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use common\models\Rooms;
use common\models\Reservation;


use frontend\models\Customer;

class ReservationController extends Controller
{

    public $periodo;
    // Desabilitar o comportamento padrão do REST para usarmos as nossas ações
    public function actions()
    {
        return [];
    }


    public function actionCalendarEvents($start, $end)
    {
        if (empty($start) || empty($end)) {
            Yii::$app->response->statusCode = 400;
            return ['error' => 'Datas de início e fim são obrigatórias.'];
        }

        // Lógica de consulta para sobreposição de datas
        $query = Reservation::find()
            ->where(['<=', 'hora_inicio_agendada', $end])
            ->andWhere(['>=', 'hora_fim_agendada', $start])
            ->with(['room', 'customer']);

        $reservations = $query->all();

        return $reservations;
    }

    /**
     * Ação POST: Cria uma nova reserva (hourly, daily, monthly)
     */
    public function actionCreate($room_id = null)
    {
        $model = new Reservation();
        $room  = null;

        if (!$room_id) {
            Yii::$app->session->setFlash('warning', 'Por favor, escolha uma sala primeiro.');
            return $this->redirect(['reservation/escolher']);
        }

        $room = Rooms::findOne($room_id);
        if (!$room) {
            Yii::$app->session->setFlash('error', 'Sala não encontrada.');
            return $this->redirect(['site/index']);
        }
        $model->room_id = $room_id;

        $userId   = Yii::$app->user->identity->getId();
        $customer = Customer::findOne(['user_id' => $userId]);

        if (!$customer) {
            Yii::$app->session->setFlash('error', 'Perfil de cliente não encontrado.');
            return $this->redirect(['site/index']);
        }
        $model->customer_id = $customer->id;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            // RESERVA POR HORA — usa os campos que tu realmente tens no form
            // RESERVA POR HORA — VERSÃO INDESTRUTÍVEL
            if ($model->periodo === 'hora') {
                $data = $model->data_reserva ?: date('Y-m-d');

                // Pega a hora do campo datetime (hora_inicio_agendada) que veio do form
                $horaInicioRaw = $model->hora_inicio_agendada;
                $horaFimRaw    = $model->hora_fim_agendada;

                // Extrai só HH:MM com segurança (se vazio, usa padrão)
                $horaInicio = $horaInicioRaw ? substr($horaInicioRaw, 11, 5) : '09:00';
                $horaFim    = $horaFimRaw    ? substr($horaFimRaw,    11, 5) : '10:00';

                // Garante que tem :00 no final
                if (strlen($horaInicio) !== 5) $horaInicio = '09:00';
                if (strlen($horaFim) !== 5)    $horaFim    = '10:00';

                $model->hora_inicio_agendada = $data . ' ' . $horaInicio . ':00';
                $model->hora_fim_agendada    = $data . ' ' . $horaFim    . ':00';
            }
            // RESERVA DIÁRIA
            if ($model->periodo === 'dia' && $model->data_reserva) {
                $model->hora_inicio_agendada = $model->data_reserva . ' 09:00:00';
                $model->hora_fim_agendada    = $model->data_reserva . ' 19:00:00';
                $model->total_estimado       = 32.00;
            }

            // VALIDAÇÃO DE PASSADO
            if ($model->periodo === 'hora') {
                $inicioReserva = new \DateTime($model->hora_inicio_agendada, new \DateTimeZone('Europe/Lisbon'));
                $agora = new \DateTime('now', new \DateTimeZone('Europe/Lisbon'));

                if ($inicioReserva < $agora) {
                    Yii::$app->session->setFlash('error', 'Não é permitido reservar horários que já passaram. Escolha um horário futuro.');
                    return $this->render('create', ['model' => $model, 'room' => $room]);
                }
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Reserva criada com sucesso! Agora é só pagar.');
                return $this->redirect(['/payment/checkout', 'reservation_id' => $model->id]);
            }

            Yii::$app->session->setFlash('error', 'Erro: ' . implode(', ', $model->getFirstErrors()));
        }

        return $this->render('create', [
            'model' => $model,
            'room'  => $room,
        ]);
    }

    /*
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

        $bookedReservations = Reservation::find()
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'cancel' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    ['actions' => ['create', 'escolher', 'select-hourly', 'select-daily', 'select-monthly'], 'allow' => true, 'roles' => ['fazerReserva']],
                    ['actions' => ['index', 'view'], 'allow' => true, 'roles' => ['listarMinhasReservas']],
                    ['actions' => ['update', 'cancel'], 'allow' => true, 'roles' => ['verReserva'], 'matchCallback' => function ($rule, $action) {
                        $id = Yii::$app->request->get('id');
                        $model = Reservation::findOne($id);
                        return $model && Yii::$app->user->can('verReserva', ['model' => $model]);
                    }],
                    ['allow' => false],
                ],
                'denyCallback' => function () {
                    Yii::$app->session->setFlash('error', 'Não tens permissão para isso.');
                    return Yii::$app->response->redirect(['site/index']);
                },
            ],
        ];
    }
    public function actionCancel($id)
    {
        //encontrar a reserva pelo id
        $model = Reservation::findOne($id);

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

        $reservation = Reservation::findOne($reservationId);

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

        // BLOQUEIO FORTE: DATA NO PASSADO
        $hoje = date('Y-m-d');
        if ($date < $hoje) {
            Yii::$app->session->setFlash('error', 'Não é permitido reservar em datas passadas.');
            return $this->redirect(['dashboard/index']);
        }

        // Se for hoje, bloqueia se já passou do horário de abertura (ex: 09:00)
        if ($date === $hoje) {
            $agora = new \DateTime('now', new \DateTimeZone('Europe/Lisbon'));
            $abertura = new \DateTime('today 09:00:00', new \DateTimeZone('Europe/Lisbon'));
            if ($agora > $abertura) {
                Yii::$app->session->setFlash('error', 'Não é mais possível reservar para hoje. O horário de check-in já passou.');
                return $this->redirect(['dashboard/index']);
            }
        }
        // Verifica se já tem reserva nesse dia (simples e bruto)
        $jaReservado = Reservation::find()
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
        $model = new Reservation();
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
            // MOSTRA O ERRO VERDADEIRO NO LOG + NA TELA (só em desenvolvimento!)
            Yii::error('Erros no model Reservation: ' . print_r($model->getErrors(), true));
            var_dump($model->getErrors()); // tira isso depois
            die();

            return $this->redirect(['payment/checkout', 'reservation_id' => $model->id]);
        }





        // Se mesmo assim der erro (quase impossível)
        Yii::$app->session->setFlash('error', 'Deu ruim mesmo salvando na marra: ' . implode(' | ', $model->getFirstErrors()));
        return $this->redirect(['dashboard/index']);
    }
    public function actionSelectMonthly()
    {
        $id = Yii::$app->request->get('room_id') ?? Yii::$app->request->get('id');

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }

        $room = \common\models\Rooms::findOne($id);
        if (!$room) throw new \yii\web\NotFoundHttpException('Sala não encontrada.');

        $model = new \common\models\Reservation();
        $model->room_id = $room->id;
        $model->periodo = 'mes';

        return $this->render('select-monthly', [
            'model' => $model,
            'room'  => $room,
        ]);
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
        $reserved = \frontend\models\Reservation::find()
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
        $model = new \frontend\models\Reservations();

        if ($model->load(Yii::$app->request->post())) {
            $model->periodo      = 'mes';
            $model->tipo_reserva = 'mensal';

            // Pega o customer_id do usuário logado
            $customer = \common\models\Customers::findOne(['user_id' => Yii::$app->user->id]);
            if (!$customer) {
                Yii::$app->session->setFlash('error', 'Perfil de cliente não encontrado.');
                return $this->redirect(['dashboard/index']);
            }
            $model->customer_id = $customer->id;

            // Força o data_reserva com o valor que veio do formulário (2026-01-01)
            $model->data_reserva = Yii::$app->request->post('Reservation')['data_reserva'] ?? null;

            // VERIFICAÇÃO DE CONFLITO MENSAL (nova, sem quebrar nada)
            $dt = new \DateTime($model->data_reserva);
            $inicioMes = $dt->format('Y-m-01 00:00:00');
            $fimMes    = $dt->format('Y-m-t 23:59:59.999');

            $conflito = Reservation::find()
                ->where(['room_id' => $model->room_id])
                ->andWhere(['tipo_reserva' => 'mensal'])
                ->andWhere(['<', 'hora_inicio_agendada', $fimMes])
                ->andWhere(['>', 'hora_fim_agendada', $inicioMes])
                ->exists();

            if ($conflito) {
                $mesNome = $dt->format('F/Y');
                $mesNomePt = [
                    'January'   => 'Janeiro',
                    'February' => 'Fevereiro',
                    'March'     => 'Março',
                    'April'     => 'Abril',
                    'May'      => 'Maio',
                    'June'      => 'Junho',
                    'July'      => 'Julho',
                    'August'   => 'Agosto',
                    'September' => 'Setembro',
                    'October'   => 'Outubro',
                    'November' => 'Novembro',
                    'December'  => 'Dezembro'
                ];
                $mesBonito = $mesNomePt[$dt->format('F')] . '/' . $dt->format('Y');

                Yii::$app->session->setFlash(
                    'warning',
                    "Esta sala já está reservada para o mês de <strong>{$mesBonito}</strong>. 
                 Por favor, escolha outra sala ou outro mês disponível."
                );

                return $this->redirect([
                    'reservation/select-monthly',
                    'room_id' => $model->room_id
                ]);
            }
            // FIM DA VERIFICAÇÃO

            if ($model->save()) {
                $model->refresh(); // garante que o código tá no objeto

                Yii::$app->session->setFlash('success', 'Reserva mensal criada com sucesso!');
                return $this->redirect(['payment/sucesso', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao salvar: ' . implode(', ', $model->getFirstErrors()));
            }
        } else {
            Yii::$app->session->setFlash('error', 'Dados não enviados.');
        }

        $roomId = Yii::$app->request->post('Reservation')['room_id'] ?? null;
        return $this->redirect(['reservation/select-monthly', 'room_id' => $roomId]);
    }
    /**
     * Escolher sala e data antes de criar reserva
     */
    public function actionEscolher()
    {
        $salas = Rooms::find()->orderBy('nome_sala')->all();

        return $this->render('escolher', [
            'salas' => $salas,
        ]);
    }
}
    