<?php

use yii\db\Migration;

class m251017_193611_criar_tabela_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(100)->notNull(),
            'email' => $this->string(100)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'role' => $this->string(30)->notNull()->defaultValue('recepcionista'),
            'status' => $this->string(20)->notNull()->defaultValue('ativo'),
            'data_criacao' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_193611_criar_tabela_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_193611_criar_tabela_users cannot be reverted.\n";

        return false;
    }
    */
}
