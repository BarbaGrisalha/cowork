<?php

use yii\db\Migration;

class m251017_193732_criar_tabela_customer_card_tokens extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = 'customer_card_tokens';
    private $parentTable = 'customers';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'gateway_token' => $this->string(255)->notNull()->unique(),
            'tipo_cartao' => $this->string(20)->notNull(),
            'ultimos_digitos' => $this->char(4)->null(),
        ]);
        $this->addForeignKey(
            'fk-customer_card_tokens-customer_id',
            $this->tableName,
            'customer_id',
            $this->parentTable,
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251017_193732_criar_tabela_customer_card_tokens cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_193732_criar_tabela_customer_card_tokens cannot be reverted.\n";

        return false;
    }
    */
}
