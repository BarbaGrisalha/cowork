<?php

use yii\db\Migration;

class m251017_193012_criar_tabela_rooms extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('rooms', [
            'id' => $this->primaryKey(),
            'nome_sala' => $this->string(100)->notNull(),
            'capacidade' => $this->integer()->notNull(),
            'descricao' => $this->text()->null(),
            'preco_hora' => $this->decimal(10, 2)->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('ativa'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_193012_criar_tabela_rooms cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_193012_criar_tabela_rooms cannot be reverted.\n";

        return false;
    }
    */
}
