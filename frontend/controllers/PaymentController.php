<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\FakeCardForm;
use frontend\models\Reservations; // Assumindo que sua Model de Reserva está em common/models
use yii\NotFoundHttpException;

class PaymentController extends Controller
{
    /**
     * Ação GET/POST: Processa o checkout e o pagamento mock.
     * @param int $reservation_id O ID da reserva a ser paga.
     */
    public function actionCheckout($reservation_id)
    {
        // 1. Validar e Carregar a Reserva
        $reservation = Reservations::findOne($reservation_id);
        if (!$reservation) {
            // Assume-se que 'NotFoundHttpException' está incluído no topo
            throw new \yii\web\NotFoundHttpException('Reserva não encontrada, cara.');
        }

        // 2. Criar o Form Model de Cartão Fake (FakeCardForm.php)
        $model = new FakeCardForm();

        // 3. Processar o Post do Formulário de Cartão
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            // --- APROVAÇÃO 100% GARANTIDA (Se o cartão for válido) ---

            $reservation->status = 'pago'; // Atualiza o status

            if ($reservation->save()) {
                Yii::$app->session->setFlash('success', 'Pagamento FAKE APROVADO! Reserva concluída.');
                // Sucesso: Redireciona e ENCERRA a função.
                return $this->redirect(['sucesso', 'id' => $reservation->id]);
            } else {
                // Se o save falhar, o código continua e o render no final exibirá o erro.
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao finalizar a reserva. Detalhes: ' . print_r($reservation->errors, true));
            }
        }

        // 🚀 O CORRETOR DE TELA BRANCA:
        // Este RETURN lida com:
        // A) GET requests (Abertura inicial da página).
        // B) POST requests com validação do cartão falha ($model->validate() == false).
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
}
