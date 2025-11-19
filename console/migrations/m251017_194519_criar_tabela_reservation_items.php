<?php

use yii\db\Migration;

class m251017_194519_criar_tabela_reservation_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'reservation_items';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'reservation_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'quantidade' => $this->integer()->notNull()->defaultValue(1),
            'preco_total' => $this->decimal(10, 2)->notNull(),
        ]);
        $this->addForeignKey('fk-res_items-reservation_id', $this->tableName, 'reservation_id', 'reservations', 'id', 'CASCADE');
        $this->addForeignKey('fk-res_items-item_id', $this->tableName, 'item_id', 'economato', 'id', 'RESTRICT'); // RESTRICT para não apagar item de economato se estiver em reserva.

        // Índice composto para evitar duplicação de item em mesma reserva
        $this->createIndex('uk-res_items-unique', $this->tableName, ['reservation_id', 'item_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_194519_criar_tabela_reservation_items cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_194519_criar_tabela_reservation_items cannot be reverted.\n";

        return false;
    }
    */
}
