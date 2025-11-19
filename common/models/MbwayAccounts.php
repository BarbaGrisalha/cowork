<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mbway_accounts".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $numero_telemovel
 *
 * @property Customers $customer
 * @property Payments[] $payments
 */
class MbwayAccounts extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mbway_accounts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'numero_telemovel'], 'required'],
            [['customer_id'], 'integer'],
            [['numero_telemovel'], 'string', 'max' => 20],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customers::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'numero_telemovel' => 'Numero Telemovel',
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
     * Gets query for [[Payments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payments::class, ['mbway_account_id' => 'id']);
    }

}
