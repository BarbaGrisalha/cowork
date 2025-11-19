<?php

use yii\db\Migration;

class m251017_192838_criar_tabela_customers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('customers', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(100)->notNull(),
            'nif' => $this->string(20)->notNull(),
            'email' => $this->string(100)->notNull()->unique(),
            'morada' => $this->string(255)->null(),
            'telefone' => $this->string(20)->null(),
            'data_registro' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'data_atualizacao' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex('idx-customers-nif', 'customers', 'nif', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_192838_criar_tabela_customers cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_192838_criar_tabela_customers cannot be reverted.\n";

        return false;
    }
    */
}
