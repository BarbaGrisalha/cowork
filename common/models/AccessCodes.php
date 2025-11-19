<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "access_codes".
 *
 * @property int $id
 * @property int $reservation_id
 * @property string $codigo
 * @property string $data_validade
 * @property string $data_criacao
 *
 * @property Reservations $reservation
 */
class AccessCodes extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_codes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reservation_id', 'codigo', 'data_validade'], 'required'],
            [['reservation_id'], 'integer'],
            [['data_validade', 'data_criacao'], 'safe'],
            [['codigo'], 'string', 'max' => 20],
            [['codigo'], 'unique'],
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
            'codigo' => 'Codigo',
            'data_validade' => 'Data Validade',
            'data_criacao' => 'Data Criacao',
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
