<?php

use yii\db\Migration;

class m251102_141632_m251102_141522_cleanup_user_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. Remover a tabela 'users' redundante
        $this->dropTable('{{%users}}');

        // 2. Remover a coluna 'email' da tabela 'customers' (para evitar duplicidade de autenticação)
        $this->dropColumn('{{%customers}}', 'email');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251102_141632_m251102_141522_cleanup_user_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251102_141632_m251102_141522_cleanup_user_tables cannot be reverted.\n";
        // 1. Recriar a tabela 'users' (Se houver necessidade futura, reverter aqui)
            // $this->createTable(...)
    
        // 2. Adicionar de volta a coluna 'email'
        $this->addColumn('{{%customers}}', 'email', $this->string(100)->notNull()->unique());
            return true;
       
    }
    */
}
