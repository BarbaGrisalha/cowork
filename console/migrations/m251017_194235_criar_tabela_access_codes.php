<?php

use yii\db\Migration;

class m251017_194235_criar_tabela_access_codes extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'access_codes';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'reservation_id' => $this->integer()->notNull(),
            'codigo' => $this->string(20)->notNull()->unique(),
            'data_validade' => $this->dateTime()->notNull(),
            'data_criacao' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->addForeignKey('fk-access_codes-reservation_id', $this->tableName, 'reservation_id', 'reservations', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_194235_criar_tabela_access_codes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_194235_criar_tabela_access_codes cannot be reverted.\n";

        return false;
    }
    */
}
