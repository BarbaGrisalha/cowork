<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "paypal_accounts".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $email_paypal
 *
 * @property Customers $customer
 * @property Payments[] $payments
 */
class PaypalAccounts extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'paypal_accounts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'email_paypal'], 'required'],
            [['customer_id'], 'integer'],
            [['email_paypal'], 'string', 'max' => 100],
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
            'email_paypal' => 'Email Paypal',
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
        return $this->hasMany(Payments::class, ['paypal_account_id' => 'id']);
    }

}
