<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "invoices".
 *
 * @property int $id
 * @property int $reservation_id
 * @property int $customer_id
 * @property string $data_emissao
 * @property float $subtotal
 * @property float $iva_percent
 * @property float $iva_valor
 * @property float $total
 * @property string $status
 *
 * @property Customers $customer
 * @property Reservations $reservation
 */
class Invoices extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['iva_percent'], 'default', 'value' => 23.00],
            [['status'], 'default', 'value' => 'emitida'],
            [['reservation_id', 'customer_id', 'subtotal', 'iva_valor', 'total'], 'required'],
            [['reservation_id', 'customer_id'], 'integer'],
            [['data_emissao'], 'safe'],
            [['subtotal', 'iva_percent', 'iva_valor', 'total'], 'number'],
            [['status'], 'string', 'max' => 30],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customers::class, 'targetAttribute' => ['customer_id' => 'id']],
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
            'customer_id' => 'Customer ID',
            'data_emissao' => 'Data Emissao',
            'subtotal' => 'Subtotal',
            'iva_percent' => 'Iva Percent',
            'iva_valor' => 'Iva Valor',
            'total' => 'Total',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customers::class, ['id' => 'customer_id']);
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
