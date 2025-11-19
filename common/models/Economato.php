<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "economato".
 *
 * @property int $id
 * @property string $nome_item
 * @property float $preco_unit
 * @property float $preco_venda
 *
 * @property ReservationItems[] $reservationItems
 * @property Reservations[] $reservations
 */
class Economato extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'economato';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nome_item', 'preco_unit', 'preco_venda'], 'required'],
            [['preco_unit', 'preco_venda'], 'number'],
            [['nome_item'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome_item' => 'Nome Item',
            'preco_unit' => 'Preco Unit',
            'preco_venda' => 'Preco Venda',
        ];
    }

    /**
     * Gets query for [[ReservationItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservationItems()
    {
        return $this->hasMany(ReservationItems::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Reservations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservations()
    {
        return $this->hasMany(Reservations::class, ['id' => 'reservation_id'])->viaTable('reservation_items', ['item_id' => 'id']);
    }
    /**
     * Alias para o campo nome_item.
     * Permite que a View acesse $item->name em vez de $item->nome_item.
     *
     * @return string
     */
    public function getName()
    {
        return $this->nome_item;
    }
    /**
     * Alias para o campo preco_venda.
     * Permite que a View acesse $item->price em vez de $item->preco_venda.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->preco_venda;
    }
}
