<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\FakeCardForm;
use frontend\models\Reservations; // Assumindo que sua Model de Reserva está em common/models
use yii\NotFoundHttpException;
// Adicione esta linha:
use yii\db\Transaction;
use frontend\models\PaymentMockForm;

class PaymentController extends Controller
{
    /**
     * Usa transações de banco para garantir que o save seja atômico.
     */
    protected function saveReservationInTransaction(Reservations $reservation)
    {
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
    }
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
}
