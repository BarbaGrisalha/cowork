<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use frontend\models\Reservations;

/**
 * Esta é a classe modelo para a tabela "payments".
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
 * @property Reservations $reservation
 */
class Payments extends ActiveRecord
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
            // Requisitos básicos (NOT NULL da sua tabela)
            [['reservation_id', 'valor', 'metodo'], 'required'],

            // Tipos de dados
            [['reservation_id', 'customer_card_token_id', 'mbway_account_id', 'paypal_account_id'], 'integer'],
            [['valor'], 'number'],
            [['data_pagamento'], 'safe'], // O DB já tem DEFAULT CURRENT_TIMESTAMP

            // Validação de strings
            [['metodo', 'status'], 'string', 'max' => 30],

            // Validação de Status (Bom para evitar status inválidos)
            [['status'], 'in', 'range' => ['pendente', 'aprovado', 'falhou', 'reembolsado']],

            // Chaves estrangeiras (É bom validar se o cartão/conta existe, mas vou focar no essencial)

            // CRUCIAL: A reserva precisa existir!
            [['reservation_id'], 'exist', 'skipOnError' => false, 'targetClass' => Reservations::class, 'targetAttribute' => ['reservation_id' => 'id']],

            // Lógica para garantir que APENAS UM método de pagamento esteja definido
            [['customer_card_token_id', 'mbway_account_id', 'paypal_account_id'], 'validatePaymentMethodKeys'],
        ];
    }

    /**
     * Validador customizado: Apenas uma FK de método de pagamento pode ser preenchida.
     * Isso evita que um pagamento tenha um token de cartão E uma conta PayPal.
     */
    public function validatePaymentMethodKeys($attribute, $params)
    {
        $keys = [
            'customer_card_token_id',
            'mbway_account_id',
            'paypal_account_id'
        ];

        $count = 0;
        foreach ($keys as $key) {
            if ($this->$key !== null && $this->$key !== '') {
                $count++;
            }
        }

        // Se a contagem for maior que 1, temos um problema de integridade.
        if ($count > 1) {
            $this->addError($attribute, 'Apenas um método de pagamento (token/conta) pode ser especificado por transação.');
        }
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reservation_id' => 'ID da Reserva',
            'customer_card_token_id' => 'Token do Cartão',
            'mbway_account_id' => 'Conta MBWay',
            'paypal_account_id' => 'Conta PayPal',
            'valor' => 'Valor Pago',
            'data_pagamento' => 'Data do Pagamento',
            'metodo' => 'Método',
            'status' => 'Status',
        ];
    }

    // --- RELACIONAMENTO ---

    /**
     * Gets query for [[Reservation]].
     * @return \yii\db\ActiveQuery
     */
    public function getReservation()
    {
        // Certifique-se de que a classe Reservations está no namespace frontend\models
        return $this->hasOne(Reservations::class, ['id' => 'reservation_id']);
    }

    // Adicione os outros métodos de relacionamento (getMbwayAccount, getCustomerCardToken, etc)
    // se precisar buscar os dados das FKs relacionadas.
}
