<?php

namespace common\components;

use yii\base\Component;

class MockPaymentService extends Component
{
    /**
     * Processa o pagamento simulado (Mock).
     * @param string $cardNumber O número do cartão FAKE completo, usado para ditar o resultado.
     */
    public function processPayment(string $cardNumber, float $amount, string $description)
    {
        // A lógica de SIMULAÇÃO deve estar aqui, sem chamar SDKs externos!

        $number = str_replace(' ', '', $cardNumber);
        $firstDigit = substr($number, 0, 1);

        // Regra FAKE API:
        if ($firstDigit === '4') { // Visa = APROVADO
            $status = 'paid';
            $reason = 'APROVADO: Simulação efetuada com sucesso.';
        } elseif ($firstDigit === '5') { // Mastercard = PENDENTE
            $status = 'pending';
            $reason = 'PENDENTE: Simulação de análise antifraude.';
        } else { // Qualquer outro = NEGADO
            $status = 'failed';
            $reason = 'NEGADO: Simulação de cartão inválido ou rejeitado.';
        }

        $transactionId = 'MOCK_TXN_' . time() . rand(100, 999);

        // Retorna a resposta que o Controller espera
        return new MockPaymentResponse($status, $reason, $transactionId);
    }
}

// Crie esta classe no mesmo arquivo ou em um arquivo PaymentResponse.php
class MockPaymentResponse
{
    private $status;
    private $reason;
    private $transactionId;

    public function __construct(string $status, string $reason, string $transactionId)
    {
        $this->status = $status;
        $this->reason = $reason;
        $this->transactionId = $transactionId;
    }

    public function isApproved(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
