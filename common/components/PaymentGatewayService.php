<?php

namespace common\components;

use yii\base\Component;
use Yii;

/**
 * Serviço de Gateway de Pagamento FAKE/Mock para fins acadêmicos.
 */
class PaymentGatewayService extends Component
{
    // A chave secreta não é usada no Mock, mas pode ficar para referência futura.
    public $secretKey;

    /**
     * Processa o pagamento SIMULADO usando o número do cartão FAKE.
     * @param string $cardNumber O número do cartão FAKE completo, usado para ditar o resultado.
     * @param float $amount O valor total da reserva (total_estimado).
     * @param string $description Descrição do pagamento.
     * @return PaymentResponse Um objeto de resposta padronizado FAKE.
     */
    public function processPayment(string $cardNumber, float $amount, string $description)
    {
        // 🚨 ESTE É O BLOCO QUE SUBSTITUI A CHAMADA REAL À API

        $number = str_replace(' ', '', $cardNumber);
        $firstDigit = substr($number, 0, 1);

        // Regra FAKE API: '4' para APROVADO, '5' para PENDENTE.
        if ($firstDigit === '4') {
            $status = 'paid';
            $reason = 'APROVADO: Simulação efetuada com sucesso.';
        } elseif ($firstDigit === '5') {
            $status = 'pending';
            $reason = 'PENDENTE: Simulação de análise antifraude.';
        } else {
            $status = 'failed';
            $reason = 'NEGADO: Simulação de cartão inválido ou rejeitado.';

            // Para testar o erro crítico: remova o bloco acima e lance uma exceção aqui.
            // throw new \Exception("Erro forçado na comunicação (Mock).");
        }

        $transactionId = 'MOCK_TXN_' . time() . rand(100, 999);

        // Retorna a resposta que o Controller espera
        return new PaymentResponse($status, $reason, $transactionId);
    }
}

/**
 * Classe de Resposta Padrão para o Mock.
 * Deve ser capaz de viver em seu próprio arquivo ou neste (para simplicidade acadêmica).
 */
class PaymentResponse
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
