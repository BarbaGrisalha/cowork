<?php

use yii\db\Migration;

class m251102_142242_m251102_142243_update_customers_and_pricing extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    { // 1. Adicionar user_id à tabela 'customers'
        $this->addColumn('{{%customers}}', 'user_id', $this->integer()->notNull()->unique()->after('id'));

        // 2. Criar a Chave Estrangeira
        $this->addForeignKey(
            'fk-customers-user_id',
            '{{%customers}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE' // Se deletar o login, deleta o registro do cliente
        );
        // 3. Criar a nova tabela 'pricing_plans'
        $this->createTable('{{%pricing_plans}}', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(100)->notNull(),
            'unidade_tempo' => $this->string(20)->notNull(), // HOUR, DAY, MONTH
            'valor' => $this->decimal(10, 2)->notNull(),
            'is_active' => $this->boolean()->defaultValue(1)->notNull(),
        ]);
        // 4. Remover 'preco_hora' da tabela 'rooms'
        $this->dropColumn('{{%rooms}}', 'preco_hora');

        // 5. Adicionar a FK 'pricing_plan_id' à tabela 'rooms'
        $this->addColumn('{{%rooms}}', 'pricing_plan_id', $this->integer()->after('descricao'));
        $this->addForeignKey(
            'fk-rooms-pricing_plan_id',
            '{{%rooms}}',
            'pricing_plan_id',
            '{{%pricing_plans}}',
            'id',
            'SET NULL' // Se o plano for deletado, o campo fica NULL (melhor que CASCADE)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251102_142242_m251102_142243_update_customers_and_pricing cannot be reverted.\n";

        return false;
    }



    public function down()
    {
        // 1. Remover FK e Coluna de rooms
        $this->dropForeignKey('fk-rooms-pricing_plan_id', '{{%rooms}}');
        $this->dropColumn('{{%rooms}}', 'pricing_plan_id');
        $this->addColumn('{{%rooms}}', 'preco_hora', $this->decimal(10, 2)->notNull());

        // 2. Remover a tabela 'pricing_plans'
        $this->dropTable('{{%pricing_plans}}');

        // 3. Remover FK e Coluna de customers
        $this->dropForeignKey('fk-customers-user_id', '{{%customers}}');
        $this->dropColumn('{{%customers}}', 'user_id');

        return true;
    }
}
