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
use frontend\models\Customer;

class PaymentController extends Controller
{
    /**
     * Usa transações de banco para garantir que o save seja atômico.
     */
    protected function saveReservationInTransaction(Reservations $reservation, ?Payments $payment = null)
    {
        /*
        $transaction = Yii::$app->db->beginTransaction(Transaction::READ_COMMITTED);
        try {
            if (!$reservation->save(false)) {
                $transaction->rollBack();
                throw new \Exception('Falha crítica ao salvar a reserva.');
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
            */
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
    /*
    public function actionCheckout($reservation_id)
    {

        $reservation = Reservations::findOne($reservation_id);
        if (!$reservation) {
            throw new NotFoundHttpException('Reserva não encontrada, cara.');
        }

        // 🚨 Instanciação do Form Model completo para a View
        $model = new FakeCardForm();

        if (Yii::$app->request->isPost) {

            // 🚨 Carrega e valida TODOS os campos (Luhn, data, CVC)
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {

                $cardNumber = $model->card_number;

                try {
                    $gateway = Yii::$app->paymentGatewayService;
                    // 🚨 CORREÇÃO DO ERRO room_name: Simplificando a descrição
                    $description = "Reserva #{$reservation->id} (Simulação Acadêmica)";

                    // $response = $gateway->processPayment($cardNumber, $reservation->amount, $description);
                    $response = $gateway->processPayment($cardNumber, $reservation->total_estimado, $description);

                    // 3. ATUALIZA O STATUS BASEADO NA RESPOSTA FAKE
                    if ($response->isApproved()) {
                        $reservation->status = 'APROVADO';
                    } elseif ($response->isPending()) {
                        $reservation->status = 'AGUARDANDO_GATEWAY';
                    } else {
                        $reservation->status = 'NEGADO';
                        // AQUI não precisamos lançar uma Exception, apenas exibir o erro.
                        Yii::$app->session->setFlash('error', 'Pagamento negado: ' . $response->getReason());
                        $reservation->status = 'FALHA';
                        $reservation->save(false);
                        return $this->redirect(['falha', 'id' => $reservation->id]);
                    }

                    // $reservation->transaction_id = $response->getTransactionId();

                    // 4. Salva a Reserva atomicamente (aprovada ou pendente)
                    $this->saveReservationInTransaction($reservation);

                    Yii::$app->session->setFlash('success', 'Simulação de Pagamento APROVADA!');
                    return $this->redirect(['sucesso', 'id' => $reservation->id]);
                } catch (\Exception $e) {
                    // Captura erro de API ou de DB
                    Yii::error($e->getMessage(), __METHOD__);
                    $reservation->status = 'ERRO';
                    $reservation->save(false);
                    Yii::$app->session->setFlash('error', 'Falha crítica: ' . $e->getMessage());
                    return $this->redirect(['falha', 'id' => $reservation->id]);
                }
            } // Fim do if ($model->load && $model->validate)
        }

        // Renderiza passando o $model completo
        return $this->render('checkout', [
            'reservation' => $reservation,
            'model' => $model, // 🚨 Variável $model agora existe para a View
        ]);
    }
    */

    public function actionCheckout($reservation_id)
    {
        $reservation = Reservations::findOne($reservation_id);
        if (!$reservation) {
            throw new NotFoundHttpException('Reserva não encontrada, cara.');
        }

        $model = new FakeCardForm();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {

                $cardNumber = $model->card_number;

                try {
                    $gateway = Yii::$app->paymentGatewayService;
                    $description = "Reserva #{$reservation->id} (Simulação Acadêmica)";
                    $response = $gateway->processPayment($cardNumber, $reservation->total_estimado, $description);

                    // ----------------------------
                    // PAGAMENTO APROVADO
                    // ----------------------------
                    if ($response->isApproved()) {

                        // Criar o registo de pagamento
                        $payment = new Payments();
                        $payment->reservation_id = $reservation->id;
                        $payment->valor = $reservation->total_estimado;
                        $payment->metodo = 'CARTAO_SIMULADO';
                        $payment->status = 'aprovado';

                        // campos opcionais (NULL)
                        $payment->customer_card_token_id = null;
                        $payment->mbway_account_id = null;
                        $payment->paypal_account_id = null;

                        // data de pagamento
                        $payment->data_pagamento = date('Y-m-d H:i:s');

                        // Validar antes de salvar
                        if (!$payment->validate()) {
                            Yii::error("Falha de validação do Payments: " . json_encode($payment->getErrors()));
                            Yii::$app->session->setFlash('error', 'Erro interno ao processar o pagamento.');
                            return $this->redirect(['falha', 'id' => $reservation->id]);
                        }

                        // atualizar status da reserva
                        $reservation->status = 'Confirmado';

                        // SALVAR TUDO ATOMICAMENTE
                        $this->saveReservationInTransaction($reservation, $payment);

                        Yii::$app->session->setFlash('success', 'Pagamento confirmado!');
                        return $this->redirect(['sucesso', 'id' => $reservation->id]);
                    }

                    // ----------------------------
                    // PAGAMENTO PENDENTE
                    // ----------------------------
                    elseif ($response->isPending()) {

                        $reservation->status = 'AGUARDANDO_GATEWAY';
                        $this->saveReservationInTransaction($reservation);

                        Yii::$app->session->setFlash('warning', 'Pagamento pendente');
                        return $this->redirect(['sucesso', 'id' => $reservation->id]);
                    }

                    // ----------------------------
                    // PAGAMENTO NEGADO
                    // ----------------------------
                    else {
                        $reservation->status = 'FALHA';
                        $this->saveReservationInTransaction($reservation);
                        Yii::$app->session->setFlash('error', 'Pagamento negado');
                        return $this->redirect(['falha', 'id' => $reservation->id]);
                    }
                } catch (\Exception $e) {

                    Yii::error('Falha crítica na transação: ' . $e->getMessage(), __METHOD__);
                    $reservation->status = 'ERRO';
                    $reservation->save(false);
                    Yii::$app->session->setFlash('error', 'Erro interno no pagamento');

                    return $this->redirect(['falha', 'id' => $reservation->id]);
                }
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
    /*
    public function actionCheckoutDaily()
    {
        $date    = Yii::$app->request->post('date');
        $room_id = Yii::$app->request->post('room_id');

        if (!$date || !$room_id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Yii::$app->session->setFlash('error', 'Dados inválidos.');
            return $this->redirect(['reservation/select-daily', 'room_id' => $room_id]);
        }

        $room = \common\models\Rooms::findOne($room_id);
        if (!$room) throw new \yii\web\NotFoundHttpException('Sala não encontrada.');

        $exists = \frontend\models\Reservations::find()
            ->where(['room_id' => $room_id, 'data_reserva' => $date])
            ->exists();
        if ($exists) {
            Yii::$app->session->setFlash('error', 'Esta data já está reservada!');
            return $this->redirect(['reservation/select-daily', 'room_id' => $room_id]);
        }

        $customer = \frontend\models\Customer::findOne(['user_id' => Yii::$app->user->id]);
        if (!$customer) {
            Yii::$app->session->setFlash('error', 'Perfil de cliente não encontrado.');
            return $this->redirect(['site/index']);
        }

        $model = new \frontend\models\Reservations();
        $model->room_id              = $room_id;
        $model->customer_id          = $customer->id;
        $model->data_reserva         = $date;
        $model->hora_inicio_agendada = $date . ' 09:00:00';  // ← CORRETO
        // $model->hora_fim_agendada    = $date . ' 19:00:00';  // ← CORRETO
        $model->total_estimado       = 32.00;
        $model->status               = 'Pendente';
        $model->tipo_reserva         = 'diaria';

        if ($model->save()) {
            return $this->redirect(['checkout', 'reservation_id' => $model->id]);
        }

        Yii::$app->session->setFlash('error', 'Erro fatal: ' . implode(' | ', $model->firstErrors));
        return $this->redirect(['reservation/select-daily', 'room_id' => $room_id]);
    }*/


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
