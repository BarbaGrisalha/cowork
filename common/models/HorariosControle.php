<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "horarios_controle".
 *
 * @property int $id
 * @property int $reservation_id
 * @property string $hora_inicio
 * @property string $hora_fim
 * @property string|null $checkin
 * @property string|null $checkout
 *
 * @property Reservations $reservation
 */
class HorariosControle extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'horarios_controle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['checkin', 'checkout'], 'default', 'value' => null],
            [['reservation_id', 'hora_inicio', 'hora_fim'], 'required'],
            [['reservation_id'], 'integer'],
            [['hora_inicio', 'hora_fim', 'checkin', 'checkout'], 'safe'],
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
            'hora_inicio' => 'Hora Inicio',
            'hora_fim' => 'Hora Fim',
            'checkin' => 'Checkin',
            'checkout' => 'Checkout',
        ];
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
