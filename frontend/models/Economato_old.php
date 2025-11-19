<?php

namespace common\models;

use Yii;

// ADICIONE AS DECLARAÇÕES DE USO (USE STATEMENTS)
// Estas são necessárias para os relacionamentos (getReservationItems e getReservations)
use common\models\Reservation;
use common\models\ReservationItems;

/**
 * This is the model class for table "economato".
 * ... (Restante do Bloco DocBlock)
 */
class Economato extends \yii\db\ActiveRecord
{
    // ... (Seu código existente: tableName, rules, attributeLabels) ...

    /**
     * Alias para o campo nome_item.
     * Permite que a View acesse $item->name em vez de $item->nome_item.
     * @return string
     */
    public function getName()
    {
        return $this->nome_item;
    }

    /**
     * Alias para o campo preco_venda.
     * Permite que a View acesse $item->price em vez de $item->preco_venda.
     * @return float
     */
    public function getPrice()
    {
        return $this->preco_venda;
    }

    /**
     * Gets query for [[ReservationItems]].
     * @return \yii\db\ActiveQuery
     */
    public function getReservationItems()
    {
        return $this->hasMany(ReservationItems::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Reservations]].
     * @return \yii\db\ActiveQuery
     */
    public function getReservations()
    {
        return $this->hasMany(Reservation::class, ['id' => 'reservation_id'])->viaTable('reservation_items', ['item_id' => 'id']);
    }
}
