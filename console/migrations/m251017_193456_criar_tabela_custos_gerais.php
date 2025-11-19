<?php

use yii\db\Migration;

class m251017_193456_criar_tabela_custos_gerais extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('custos_gerais', [
            'id' => $this->primaryKey(),
            'descricao' => $this->string(255)->notNull(),
            'valor' => $this->decimal(10, 2)->notNull(),
            'data_registro' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_193456_criar_tabela_custos_gerais cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_193456_criar_tabela_custos_gerais cannot be reverted.\n";

        return false;
    }
    */
}
