<?php

use yii\db\Migration;

class m251017_194605_criar_tabela_reservation_room_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'reservation_room_items';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'reservation_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'quantidade' => $this->integer()->notNull()->defaultValue(1),
            'preco_total' => $this->decimal(10, 2)->notNull(),
        ]);
        $this->addForeignKey('fk-res_room_items-reservation_id', $this->tableName, 'reservation_id', 'reservations', 'id', 'CASCADE');
        $this->addForeignKey('fk-res_room_items-item_id', $this->tableName, 'item_id', 'room_items', 'id', 'RESTRICT');

        // Índice composto para evitar duplicação
        $this->createIndex('uk-res_room_items-unique', $this->tableName, ['reservation_id', 'item_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_194605_criar_tabela_reservation_room_items cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_194605_criar_tabela_reservation_room_items cannot be reverted.\n";

        return false;
    }
    */
}
