<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "payments".
 *
 * @property int $id
 * @property int $reservation_id
 * @property int|null $customer_card_token_id
 * @property int|null $mbway_account_id
 * @property int|null $paypal_account_id
 * @property float $valor
 * @property string $data_pagamento
 * @property string $metodo
 * @property string $status
 *
 * @property CustomerCardTokens $customerCardToken
 * @property MbwayAccounts $mbwayAccount
 * @property PaypalAccounts $paypalAccount
 * @property Reservation $reservation
 */

use common\models\CustomerCardTokens;
use common\models\MbwayAccounts;
use common\models\PaypalAccounts;
use common\models\Reservations;

class Payment extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_card_token_id', 'mbway_account_id', 'paypal_account_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'pendente'],
            [['reservation_id', 'valor', 'metodo'], 'required'],
            [['reservation_id', 'customer_card_token_id', 'mbway_account_id', 'paypal_account_id'], 'integer'],
            [['valor'], 'number'],
            [['data_pagamento'], 'safe'],
            [['metodo', 'status'], 'string', 'max' => 30],
            [['customer_card_token_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerCardTokens::class, 'targetAttribute' => ['customer_card_token_id' => 'id']],
            [['reservation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reservation::class, 'targetAttribute' => ['reservation_id' => 'id']],
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
            'customer_card_token_id' => 'Customer Card Token ID',
            'mbway_account_id' => 'Mbway Account ID',
            'paypal_account_id' => 'Paypal Account ID',
            'valor' => 'Valor',
            'data_pagamento' => 'Data Pagamento',
            'metodo' => 'Metodo',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[CustomerCardToken]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerCardToken()
    {
        return $this->hasOne(CustomerCardTokens::class, ['id' => 'customer_card_token_id']);
    }



    /**
     * Gets query for [[Reservation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservation()
    {
        return $this->hasOne(Reservation::class, ['id' => 'reservation_id']);
    }
}
