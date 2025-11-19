<?php

namespace common\models;

use Yii;


/**
 * This is the model class for table "room_items".
 *
 * @property int $id
 * @property string $nome_item
 * @property string|null $descricao
 * @property float $preco_extra
 *
 * @property ReservationRoomItems[] $reservationRoomItems
 * @property Reservations[] $reservations
 */
class RoomItems extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'room_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descricao'], 'default', 'value' => null],
            [['preco_extra'], 'default', 'value' => 0.00],
            [['nome_item'], 'required'],
            [['preco_extra'], 'number'],
            [['nome_item'], 'string', 'max' => 100],
            [['descricao'], 'string', 'max' => 255],
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
            'descricao' => 'Descricao',
            'preco_extra' => 'Preco Extra',
        ];
    }

    /**
     * Gets query for [[ReservationRoomItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservationRoomItems()
    {
        return $this->hasMany(ReservationRoomItems::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Reservations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservations()
    {
        return $this->hasMany(Reservations::class, ['id' => 'reservation_id'])->viaTable('reservation_room_items', ['item_id' => 'id']);
    }
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
     * Alias para o campo preco_extra.
     * Permite que a View acesse $item->price em vez de $item->preco_extra.
     * @return float
     */
    public function getPrice()
    {
        return $this->preco_extra;
    }
}
