<?php

use yii\db\Migration;

class m251017_194025_criar_tabela_reservations extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'reservations';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'room_id' => $this->integer()->notNull(),
            'data_reserva' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'hora_inicio_agendada' => $this->dateTime()->notNull(),
            'hora_fim_agendada' => $this->dateTime()->notNull(),
            'total_estimado' => $this->decimal(10, 2)->notNull()->defaultValue(0.00),
            'status' => $this->string(30)->notNull()->defaultValue('pendente'),
        ]);
        // FKs
        $this->addForeignKey('fk-reservations-customer_id', $this->tableName, 'customer_id', 'customers', 'id', 'CASCADE');
        $this->addForeignKey('fk-reservations-room_id', $this->tableName, 'room_id', 'rooms', 'id', 'CASCADE');
        // Chave Única de Disponibilidade
        $this->createIndex(
            'uk-reserva_sala_tempo',
            $this->tableName,
            ['room_id', 'hora_inicio_agendada', 'hora_fim_agendada'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_194025_criar_tabela_reservations cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_194025_criar_tabela_reservations cannot be reverted.\n";

        return false;
    }
    */
}
