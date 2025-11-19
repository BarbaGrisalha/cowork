<?php
// common/Payment/Gateway/MockPaymentGateway.php

namespace App\Payment\Gateway;

use App\Payment\DTO\PaymentResponseDTO;
use App\Payment\Exception\PaymentGatewayException;

/**
 * Implementação Mock/Simulada da Interface. 
 * Perfeita para desenvolvimento e TCC.
 */
class MockPaymentGateway implements PaymentGatewayInterface
{
    public function charge(string $token, float $amount, int $customerId): PaymentResponseDTO
    {
        // 1. Simulação de Cartão Recusado
        if (str_contains($token, 'FAIL')) {
            throw new PaymentGatewayException("Transação recusada: Código de erro 5003.");
        }

        // 2. Simulação de Sucesso
        // No TCC, isso simula a chamada HTTP de sucesso à API externa.
        $gatewayId = 'TXN_' . time() . '_' . $customerId;

        // 3. Retorna o DTO com o resultado.
        return new PaymentResponseDTO($gatewayId, 'APPROVED');
    }
}
