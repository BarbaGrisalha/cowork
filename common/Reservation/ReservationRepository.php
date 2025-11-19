<?php
// common/Reservation/Repository/ReservationRepository.php

namespace App\Reservation\Repository;

use common\models\Reservation as ReservationModel; // Seu modelo ActiveRecord do Yii2
use yii\db\Transaction;
use Yii;

/**
 * Repository (Camada de Persistência) para a tabela 'reservations'.
 * Encapsula a lógica de DB.
 */
class ReservationRepository
{
    // Métodos para controle transacional (essenciais para o PaymentService)
    public function beginTransaction(): Transaction
    {
        return Yii::$app->db->beginTransaction();
    }

    // ... commit() e rollback()

    /**
     * @param int $id ID da reserva.
     * @return ReservationModel|null
     */
    public function find(int $id): ?ReservationModel
    {
        return ReservationModel::findOne($id);
    }

    /**
     * Salva a entidade no DB (ActiveRecord do Yii2).
     * @param ReservationModel $reservation
     * @return bool
     */
    public function save(ReservationModel $reservation): bool
    {
        // No Yii2, o save() já faz o INSERT ou UPDATE.
        if (!$reservation->save()) {
            // Lógica de log de erro, se necessário
            return false;
        }
        return true;
    }

    // ... findByStatus(string $status), findByCustomer(int $customerId), etc.
}
