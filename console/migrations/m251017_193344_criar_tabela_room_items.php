<?php

use yii\db\Migration;

class m251017_193344_criar_tabela_room_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('room_items', [
            'id' => $this->primaryKey(),
            'nome_item' => $this->string(100)->notNull(),
            'descricao' => $this->string(255)->null(),
            'preco_extra' => $this->decimal(10, 2)->notNull()->defaultValue(0.00),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_193344_criar_tabela_room_items cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_193344_criar_tabela_room_items cannot be reverted.\n";

        return false;
    }
    */
}
