<?php
// common/DTO/PaymentRequestData.php

namespace App\Payment\DTO;

/**
 * Data Transfer Object para segurar e validar dados de requisição de pagamento.
 * Garante que os dados de entrada são do tipo e formato esperados.
 */
class PaymentRequestData
{
    private string $paymentToken;
    private string $method;
    private array $allowedMethods = ['CARD', 'MBWAY', 'PAYPAL'];

    /**
     * @param string $paymentToken O token de uso único da operadora (NÃO o cartão).
     * @param string $method O método de pagamento escolhido.
     * @throws \InvalidArgumentException Se os dados forem inválidos.
     */
    public function __construct(?string $paymentToken, ?string $method)
    {
        if (empty($paymentToken) || !is_string($paymentToken)) {
            throw new \InvalidArgumentException("Token de pagamento é obrigatório.");
        }
        if (empty($method) || !in_array(strtoupper($method), $this->allowedMethods)) {
            throw new \InvalidArgumentException("Método de pagamento inválido ou ausente.");
        }

        $this->paymentToken = $paymentToken;
        $this->method = strtoupper($method);
    }

    // Métodos de acesso (Imutabilidade: sem settes)
    public function getPaymentToken(): string
    {
        return $this->paymentToken;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}