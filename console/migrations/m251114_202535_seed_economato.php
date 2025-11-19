<?php

use yii\db\Migration;

class m251114_202535_seed_economato extends Migration
{
    private $tableName = '{{%economato}}';

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // Array de dados para inserção (Exemplo com preço de custo e venda)
        $items = [
            [
                'nome_item' => 'Café Espresso',
                'preco_unit' => 0.50,  // Custo unitário
                'preco_venda' => 1.50   // Preço de venda para o cliente
            ],
            [
                'nome_item' => 'Água',
                'preco_unit' => 0.40,
                'preco_venda' => 1.00
            ],
            [
                'nome_item' => 'Chá',
                'preco_unit' => 0.60,
                'preco_venda' => 1.80
            ],
            [
                'nome_item' => 'Snacks',
                'preco_unit' => 0.60,
                'preco_venda' => 1.80
            ],
        ];

        // Itera e insere os dados
        foreach ($items as $item) {
            $this->insert($this->tableName, $item);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // Remove os dados inseridos para o rollback
        $this->delete($this->tableName, ['nome_item' => 'Chá']);
        $this->delete($this->tableName, ['nome_item' => 'Água']);
        $this->delete($this->tableName, ['nome_item' => 'Café Espresso']);
        $this->delete($this->tableName, ['nome_item' => 'Snacks']);
    }
}
