<?php

use yii\db\Migration;

class m251017_194434_criar_tabela_invoices extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'invoices';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'reservation_id' => $this->integer()->notNull(),
            'customer_id' => $this->integer()->notNull(),
            'data_emissao' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'subtotal' => $this->decimal(10, 2)->notNull(),
            'iva_percent' => $this->decimal(5, 2)->notNull()->defaultValue(23.00),
            'iva_valor' => $this->decimal(10, 2)->notNull(),
            'total' => $this->decimal(10, 2)->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('emitida'),
        ]);
        $this->addForeignKey('fk-invoices-reservation_id', $this->tableName, 'reservation_id', 'reservations', 'id', 'CASCADE');
        $this->addForeignKey('fk-invoices-customer_id', $this->tableName, 'customer_id', 'customers', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_194434_criar_tabela_invoices cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_194434_criar_tabela_invoices cannot be reverted.\n";

        return false;
    }
    */
}
