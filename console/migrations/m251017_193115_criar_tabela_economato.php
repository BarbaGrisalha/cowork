<?php

use yii\db\Migration;

class m251017_193115_criar_tabela_economato extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('economato', [
            'id' => $this->primaryKey(),
            'nome_item' => $this->string(100)->notNull(),
            'preco_unit' => $this->decimal(10, 2)->notNull(),
            'preco_venda' => $this->decimal(10, 2)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_193115_criar_tabela_economato cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_193115_criar_tabela_economato cannot be reverted.\n";

        return false;
    }
    */
}
