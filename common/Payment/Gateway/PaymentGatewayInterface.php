<?php
// common/Payment/Gateway/PaymentGatewayInterface.php

namespace App\Payment\Gateway;

use App\Payment\DTO\PaymentResponseDTO; // Vamos criar este DTO para a resposta
use App\Payment\Exception\PaymentGatewayException; // Vamos criar esta Exceção

/**
 * Contrato para qualquer implementação de Gateway de Pagamento.
 * Garante que o PaymentCreationService possa trocar de operadora facilmente.
 */
interface PaymentGatewayInterface
{
    /**
     * Processa a cobrança real na operadora de pagamento.
     * * @param string $token O token de uso único do cartão/método.
     * @param float $amount O valor total a ser cobrado.
     * @param int $customerId O ID do cliente (para referência da operadora).
     * @return PaymentResponseDTO Dados da transação (ID externo, status, etc.).
     * @throws PaymentGatewayException Em caso de cartão recusado, erro de rede, etc.
     */
    public function charge(string $token, float $amount, int $customerId): PaymentResponseDTO;
}