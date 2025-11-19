<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customer_card_tokens".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $gateway_token
 * @property string $tipo_cartao
 * @property string|null $ultimos_digitos
 *
 * @property Customers $customer
 * @property Payments[] $payments
 */
class CustomerCardTokens extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_card_tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ultimos_digitos'], 'default', 'value' => null],
            [['customer_id', 'gateway_token', 'tipo_cartao'], 'required'],
            [['customer_id'], 'integer'],
            [['gateway_token'], 'string', 'max' => 255],
            [['tipo_cartao'], 'string', 'max' => 20],
            [['ultimos_digitos'], 'string', 'max' => 4],
            [['gateway_token'], 'unique'],
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
            'gateway_token' => 'Gateway Token',
            'tipo_cartao' => 'Tipo Cartao',
            'ultimos_digitos' => 'Ultimos Digitos',
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
        return $this->hasMany(Payments::class, ['customer_card_token_id' => 'id']);
    }

}
