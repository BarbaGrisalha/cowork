<?php
// common/Payment/Service/PaymentCreationService.php

namespace App\Payment\Service;

use App\Reservation\Repository\ReservationRepository;
use App\Payment\Repository\PaymentRepository;
use App\Payment\Gateway\PaymentGatewayInterface; // Interface de comunicação externa
//use backend\models\Reservation as ReservationModel;
//use backend\models\Payment as PaymentModel;
use Yii;
use common\models\Reservation as ReservationModel;
use common\models\Payment as PaymentModel;
// ... Outras Entidades/Modelos (CustomerCardToken, Invoice, etc.)

/**
 * Serviço que orquestra a transação de pagamento de ponta a ponta.
 * Garante a atomicidade (ACID) entre a chamada de API e o salvamento no DB.
 */
class PaymentCreationService
{
    private $reservationRepository;
    private $paymentRepository;
    private $paymentGateway; // Ex: StripeGateway ou MbWayGateway

    public function __construct(
        ReservationRepository $reservationRepository,
        PaymentRepository $paymentRepository,
        PaymentGatewayInterface $paymentGateway // Implementação real da API
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->paymentRepository = $paymentRepository;
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Executa a cobrança e atualiza o status das tabelas de forma transacional.
     * @param int $reservationId ID da reserva.
     * @param string $paymentToken Token do cartão/método.
     * @param string $method Método de pagamento.
     * @return ReservationModel
     * @throws \Exception Em caso de qualquer falha.
     */
    public function executePayment(int $reservationId, string $paymentToken, string $method): ReservationModel //linha 44
    {
        /** @var ReservationModel $reservation */
        $reservation = $this->reservationRepository->find($reservationId);

        // Inicia a transação de DB antes de tocar na API externa.
        $transaction = $this->reservationRepository->beginTransaction();

        try {
            // A. CHAMADA EXTERNA (AO GATEWAY)
            $paymentResponse = $this->paymentGateway->charge(
                $paymentToken,
                $reservation->total_estimado, // Valor da cobrança
                $reservation->customer_id
            );

            // B. CRIAÇÃO DO REGISTRO DE PAGAMENTO (Tabela `payments`)
            $payment = new PaymentModel();
            $payment->reservation_id = $reservationId;
            $payment->valor = $reservation->total_estimado;
            $payment->metodo = $method;
            $payment->status = 'APPROVED';
            $payment->gateway_transaction_id = $paymentResponse->getGatewayId();

            if (!$this->paymentRepository->save($payment)) {
                throw new \RuntimeException("Falha ao salvar o registro de pagamento.");
            }

            // C. ATUALIZAÇÃO DO STATUS DA RESERVATION (Tabela `reservations`)
            $reservation->status = 'PAID';

            if (!$this->reservationRepository->save($reservation)) { //linha 75
                throw new \RuntimeException("Falha ao atualizar o status da Reserva.");
            }

            // D. COMMIT: Sucesso total. A transação é finalizada no DB.
            $transaction->commit();

            return $reservation;
        } catch (\Exception $e) {
            // ROLLBACK: Se algo falhar (API ou DB), desfazemos tudo no DB.
            $transaction->rollBack();
            Yii::error("Transação de pagamento #{$reservationId} falhou e foi desfeita: " . $e->getMessage());

            // Relançamos a exceção para o Controller tratar o erro HTTP
            throw $e;
        }
    }
}
