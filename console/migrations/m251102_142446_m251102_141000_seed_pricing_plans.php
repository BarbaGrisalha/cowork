<?php

use yii\db\Migration;

class m251102_142446_m251102_141000_seed_pricing_plans extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%pricing_plans}}', [
            'nome' => 'Plano por Hora',
            'unidade_tempo' => 'HOUR',
            'valor' => 7.00, // Exemplo de R$ 5,00 por hora
        ]);

        $this->insert('{{%pricing_plans}}', [
            'nome' => 'Plano Diário',
            'unidade_tempo' => 'DAY',
            'valor' => 32.00, // Exemplo de R$ 30,00 por dia
        ]);

        $this->insert('{{%pricing_plans}}', [
            'nome' => 'Plano Mensal',
            'unidade_tempo' => 'MONTH',
            'valor' => 225.00, // Exemplo de R$ 500,00 por mês
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove os dados inseridos. É crucial usar o WHERE para deletar apenas os dados desta seed.
        $this->delete('{{%pricing_plans}}', ['unidade_tempo' => ['HOUR', 'DAY', 'MONTH']]);
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251102_142446_m251102_141000_seed_pricing_plans cannot be reverted.\n";

        return false;
    }
    */
}
