<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "reservation_room_items".
 *
 * @property int $id
 * @property int $reservation_id
 * @property int $item_id
 * @property int $quantidade
 * @property float $preco_total
 *
 * @property RoomItems $item
 * @property Reservations $reservation
 */
class ReservationRoomItems extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reservation_room_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quantidade'], 'default', 'value' => 1],
            [['reservation_id', 'item_id', 'preco_total'], 'required'],
            [['reservation_id', 'item_id', 'quantidade'], 'integer'],
            [['preco_total'], 'number'],
            [['reservation_id', 'item_id'], 'unique', 'targetAttribute' => ['reservation_id', 'item_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => RoomItems::class, 'targetAttribute' => ['item_id' => 'id']],
            [['reservation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reservations::class, 'targetAttribute' => ['reservation_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reservation_id' => 'Reservation ID',
            'item_id' => 'Item ID',
            'quantidade' => 'Quantidade',
            'preco_total' => 'Preco Total',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(RoomItems::class, ['id' => 'item_id']);
    }

    /**
     * Gets query for [[Reservation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservation()
    {
        return $this->hasOne(Reservations::class, ['id' => 'reservation_id']);
    }

}
