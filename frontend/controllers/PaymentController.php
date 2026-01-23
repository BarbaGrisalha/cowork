<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\FakeCardForm;
use frontend\models\Reservations;
use yii\NotFoundHttpException;
use yii\db\Transaction;
use frontend\models\PaymentMockForm;
use frontend\models\Payments;
use common\models\Rooms;
use common\models\Customer;

class PaymentController extends Controller
{
    /**
     * Usa transações de banco para garantir que o save seja atômico.
     */
    protected function saveReservationInTransaction(Reservations $reservation, ?Payments $payment = null)
    {

        // Crie a transação
        $transaction = Yii::$app->db->beginTransaction(Transaction::READ_COMMITTED);
        try {
            // 1. Tentar salvar o Pagamento (se ele foi passado)
            if ($payment !== null && !$payment->save(false)) { // save(false) para ignorar validação se a gente já validou antes
                $transaction->rollBack();
                // Adiciono os erros de Pagamento na exceção
                $errors = $payment->getErrors();
                throw new \Exception('Falha crítica ao salvar o PAGAMENTO no DB: ' . json_encode($errors));
            }

            // 2. Tentar salvar a Reserva
            if (!$reservation->save(false)) {
                $transaction->rollBack();
                throw new \Exception('Falha crítica ao salvar a RESERVA no DB.');
            }

            // 3. Sucesso! Commit
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    public function actionCheckout($reservation_id)
    {
        $reservation = Reservations::findOne($reservation_id);
        if (!$reservation) {
            throw new NotFoundHttpException('Reserva não encontrada, cara.');
        }

        $model = new FakeCardForm();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {

                $cardNumber = preg_replace('/\D/', '', $model->card_number); // limpa o número

                // === SIMULAÇÃO ACADÊMICA CONTROLADA ===
                // Cartões de teste conhecidos (padrão em projetos acadêmicos e gateways reais de teste)
                $approvedCards = [
                    '4111111111111111', // Visa teste clássico
                    '4242424242424242', // Visa teste Stripe
                    '5555555555554444', // Mastercard teste
                    '378282246310005',  // Amex teste
                ];

                if (in_array($cardNumber, $approvedCards)) {
                    // ---------------------------- PAGAMENTO APROVADO (simulado) ----------------------------
                    $payment = new Payments();
                    $payment->reservation_id = $reservation->id;
                    $payment->valor = $reservation->total_estimado;
                    $payment->metodo = 'CARTAO_SIMULADO';
                    $payment->status = 'aprovado';
                    $payment->data_pagamento = date('Y-m-d H:i:s');

                    $reservation->status = 'Confirmado';

                    $this->saveReservationInTransaction($reservation, $payment);
                    $reservation->refresh();
                    Yii::$app->session->setFlash('success', 'Pagamento simulado com sucesso! Reserva confirmada.');
                    return $this->redirect(['sucesso', 'id' => $reservation->id]);
                } else {
                    // ---------------------------- PAGAMENTO NEGADO (simulado) ----------------------------
                    $reservation->status = 'FALHA';
                    $this->saveReservationInTransaction($reservation);

                    Yii::$app->session->setFlash('error', 'Pagamento negado: Cartão não aprovado na simulação.');
                    return $this->redirect(['falha', 'id' => $reservation->id]);
                }
                // === FIM DA SIMULAÇÃO ===
            }
        }

        // GET → carregar página normalmente
        return $this->render('checkout', [
            'reservation' => $reservation,
            'model' => $model,
        ]);
    }


    public function actionSucesso($id)
    {
        $reservation = Reservations::findOne($id);
        if (!$reservation) {
            throw new NotFoundHttpException('Reserva não encontrada.');
        }
        return $this->render('sucesso', ['reservation' => $reservation]);
    }
    /**
     * Action para exibir a página de falha de pagamento.
     * @param int $id O ID da reserva que falhou.
     */
    public function actionFalha($id)
    {
        // Carrega a reserva para exibir detalhes na view
        $reservation = Reservations::findOne($id);

        // Use a classe NotFoundHttpException que está importada no topo do seu Controller
        if (!$reservation) {
            throw new NotFoundHttpException('Reserva não encontrada após a falha.');
        }

        // A view 'falha' pode exibir a mensagem de erro que você setou no flash 
        // e dar opções para o cliente (tentar novamente, contato, etc.).
        return $this->render('falha', ['reservation' => $reservation]);
    }
    /**
     * NOVA ACTION: Recebe a reserva diária e redireciona pro checkout normal
     */

    public function actionCheckoutDaily()
    {
        $date    = Yii::$app->request->post('date');
        $room_id = Yii::$app->request->post('room_id');

        if (!$date || !$room_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Yii::$app->session->setFlash('error', 'Escolha uma data válida!');
            return $this->redirect(['dashboard/index']);
        }

        $date = date('Y-m-d', strtotime($date));
        if ($date < date('Y-m-d')) {
            Yii::$app->session->setFlash('error', 'Não pode reservar no passado!');
            return $this->redirect(['dashboard/index']);
        }

        // VERIFICAÇÃO INTELIGENTE: tem qualquer reserva nesse dia (horária, diária ou mensal)?
        $jaTemReserva = Reservations::find()
            ->where(['room_id' => $room_id])
            ->andWhere(['LIKE', 'hora_inicio_agendada', $date . '%'])  // qualquer reserva que comece nesse dia
            ->andWhere(['<>', 'status', 'cancelada'])
            ->exists();

        if ($jaTemReserva) {
            // MENSAGEM LIMPA E PROFISSIONAL — EXATAMENTE O QUE VOCÊ QUER
            Yii::$app->session->setFlash('error', 'Esta sala já está reservada neste dia. Escolha outra data ou sala.');
            return $this->redirect(['dashboard/index']);
        }

        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        if (!$customer) {
            Yii::$app->session->setFlash('error', 'Perfil não encontrado.');
            return $this->redirect(['site/index']);
        }

        $model = new Reservations();
        $model->room_id              = $room_id;
        $model->customer_id          = $customer->id;
        $model->data_reserva         = date('Y-m-d H:i:s');
        $model->hora_inicio_agendada = $date . ' 09:00:00';  // horário oficial do coworking
        $model->hora_fim_agendada    = $date . ' 19:00:00';
        $model->total_estimado       = 32.00;
        $model->status               = 'pendente';
        $model->tipo_reserva         = 'diaria';

        // Salva com validação desligada (pra não dar erro de unique se já existir)
        if ($model->save(false)) {
            return $this->redirect(['payment/checkout', 'reservation_id' => $model->id]);
        }

        Yii::$app->session->setFlash('error', 'Erro ao criar reserva. Tente novamente.');
        return $this->redirect(['dashboard/index']);
    }

    public function actionCheckoutMonthly()
    {
        $date    = Yii::$app->request->post('date');
        $room_id = Yii::$app->request->post('room_id');

        if (!$date || !$room_id) {
            Yii::$app->session->setFlash('error', 'Dados inválidos!');
            return $this->redirect(['dashboard/index']);
        }

        $inicio = date('Y-m-01', strtotime($date));
        $fim    = date('Y-m-t', strtotime($date));

        // Verifica se tem qualquer reserva no mês
        $jaTemReserva = Reservations::find()
            ->where(['room_id' => $room_id])
            ->andWhere(['>=', 'hora_inicio_agendada', $inicio . ' 00:00:00'])
            ->andWhere(['<=', 'hora_inicio_agendada', $fim . ' 23:59:59'])
            ->andWhere(['<>', 'status', 'cancelada'])
            ->exists();

        if ($jaTemReserva) {
            Yii::$app->session->setFlash('error', 'Este mês já tem uma ou mais reservas nesta sala. Escolha outro período.');
            return $this->redirect(['dashboard/index']);
        }

        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        if (!$customer) return $this->redirect(['site/index']);

        $model = new Reservations();
        $model->room_id              = $room_id;
        $model->customer_id          = $customer->id;
        $model->data_reserva         = date('Y-m-d H:i:s');
        $model->hora_inicio_agendada = $inicio . ' 09:00:00';
        $model->hora_fim_agendada    = $fim . ' 19:00:00';
        $model->total_estimado       = 800.00;
        $model->status               = 'pendente';
        $model->tipo_reserva         = 'mensal';

        if ($model->save(false)) {
            return $this->redirect(['payment/checkout', 'reservation_id' => $model->id]);
        }

        Yii::$app->session->setFlash('error', 'Erro ao reservar o mês.');
        return $this->redirect(['dashboard/index']);
    }
}
