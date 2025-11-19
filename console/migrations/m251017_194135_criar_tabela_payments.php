<?php

use yii\db\Migration;

class m251017_194135_criar_tabela_payments extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'payments';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'reservation_id' => $this->integer()->notNull(),
            // Normalização: Apenas 1 destas 3 pode ser notNull() na validação. Na DB deixamos NULLable.
            'customer_card_token_id' => $this->integer()->null(),
            'mbway_account_id' => $this->integer()->null(),
            'paypal_account_id' => $this->integer()->null(),
            'valor' => $this->decimal(10, 2)->notNull(),
            'data_pagamento' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'metodo' => $this->string(30)->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('pendente'),
        ]);

        $this->addForeignKey('fk-payments-reservation_id', $this->tableName, 'reservation_id', 'reservations', 'id', 'CASCADE');
        $this->addForeignKey('fk-payments-card_token_id', $this->tableName, 'customer_card_token_id', 'customer_card_tokens', 'id', 'SET NULL');
        $this->addForeignKey('fk-payments-mbway_id', $this->tableName, 'mbway_account_id', 'mbway_accounts', 'id', 'SET NULL');
        $this->addForeignKey('fk-payments-paypal_id', $this->tableName, 'paypal_account_id', 'paypal_accounts', 'id', 'SET NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_194135_criar_tabela_payments cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_194135_criar_tabela_payments cannot be reverted.\n";

        return false;
    }
    */
}
