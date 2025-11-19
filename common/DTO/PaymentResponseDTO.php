<?php
// common/Payment/DTO/PaymentResponseDTO.php

namespace App\Payment\DTO;

/**
 * DTO que contém o resultado retornado pelo Gateway de Pagamento.
 */
class PaymentResponseDTO
{
    private string $gatewayId;
    private string $status;

    public function __construct(string $gatewayId, string $status)
    {
        $this->gatewayId = $gatewayId;
        $this->status = $status;
    }

    public function getGatewayId(): string
    {
        return $this->gatewayId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
