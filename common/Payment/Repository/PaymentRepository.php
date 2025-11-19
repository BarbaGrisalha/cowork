<?php
// common/Payment/Repository/PaymentRepository.php

//namespace App\Payment\Repository;
namespace App\Payment\Repository;


use common\models\Payment as PaymentModel; // Assumindo que você tem este modelo ActiveRecord/Entidade

/**
 * Este é o modelo ActiveRecord para a tabela "payments".
 *
 * @property int $id
 * @property int $reservation_id
 * @property float $valor
 * @property string $metodo
 * @property string $status
 * @property string $data_pagamento
 */
class PaymentRepository
{
    /**
     * Salva ou atualiza um registro de pagamento no DB.
     * @param PaymentModel $payment
     * @return bool
     */
    public function save(PaymentModel $payment): bool
    {
        // No Yii2, o save() é o método mágico do ActiveRecord
        if (!$payment->save()) {
            // No mundo real, você logaria os erros de validação ($payment->getErrors())
            return false;
        }
        return true;
    }

    /**
     * Encontra um pagamento pelo ID (exemplo de método)
     * @param int $id
     * @return PaymentModel|null
     */
    public function find(int $id): ?PaymentModel
    {
        return PaymentModel::findOne($id);
    }

    // Outros métodos de busca viriam aqui...
}
