<?php

use yii\db\Migration;

class m251017_193841_criar_tabela_mbway_accounts extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'mbway_accounts';
    private $parentTable = 'customers';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'numero_telemovel' => $this->string(20)->notNull(),
        ]);
        $this->addForeignKey(
            'fk-mbway_accounts-customer_id',
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
        echo "m251017_193841_criar_tabela_mbway_accounts cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_193841_criar_tabela_mbway_accounts cannot be reverted.\n";

        return false;
    }
    */
}
