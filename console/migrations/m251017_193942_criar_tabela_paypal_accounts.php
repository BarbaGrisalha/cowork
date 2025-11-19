<?php

use yii\db\Migration;

class m251017_193942_criar_tabela_paypal_accounts extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'paypal_accounts';
    private $parentTable = 'customers';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'email_paypal' => $this->string(100)->notNull(),
        ]);
        $this->addForeignKey(
            'fk-paypal_accounts-customer_id',
            $this->tableName,
            'customer_id',
            $this->parentTable,
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_193942_criar_tabela_paypal_accounts cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_193942_criar_tabela_paypal_accounts cannot be reverted.\n";

        return false;
    }
    */
}
