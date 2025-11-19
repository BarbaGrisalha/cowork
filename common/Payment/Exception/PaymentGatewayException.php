<?php
// common/Payment/Exception/PaymentGatewayException.php

namespace App\Payment\Exception;

/**
 * Exceção específica para falhas de comunicação ou resposta de um Gateway de Pagamento.
 */
class PaymentGatewayException extends \RuntimeException
{
    // Apenas estende a exceção base para dar um nome mais específico ao erro
}
