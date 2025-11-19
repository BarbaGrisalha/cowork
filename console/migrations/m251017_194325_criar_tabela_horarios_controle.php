<?php

use yii\db\Migration;

class m251017_194325_criar_tabela_horarios_controle extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'horarios_controle';

public function safeUp()
{
    $this->createTable($this->tableName, [
        'id' => $this->primaryKey(),
        'reservation_id' => $this->integer()->notNull(),
        'hora_inicio' => $this->dateTime()->notNull(),
        'hora_fim' => $this->dateTime()->notNull(),
        'checkin' => $this->dateTime()->null(),
        'checkout' => $this->dateTime()->null(),
    ]);
    $this->addForeignKey('fk-horarios_controle-reservation_id', $this->tableName, 'reservation_id', 'reservations', 'id', 'CASCADE');
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_194325_criar_tabela_horarios_controle cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_194325_criar_tabela_horarios_controle cannot be reverted.\n";

        return false;
    }
    */
}
