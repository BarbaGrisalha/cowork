<?php

use yii\db\Migration;

class m251114_202348_seed_room_items extends Migration
{
    private $tableName = '{{%room_items}}';

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // 1. Array de dados para inserção
        $items = [
            [
                'nome_item' => 'Projetor HD',
                'descricao' => 'Projetor de alta definição para apresentações.',
                'preco_extra' => 10.00
            ],
            [
                'nome_item' => 'Webcam Profissional',
                'descricao' => 'Webcam com microfone integrado de alta qualidade.',
                'preco_extra' => 5.00
            ],
            [
                'nome_item' => 'Microfone Profissional',
                'descricao' => 'Microfone de lapela ou de mesa com cancelamento de ruído.',
                'preco_extra' => 7.50
            ],
            [
                'nome_item' => 'Tela Grande',
                'descricao' => 'Tela retrátil de projeção de 120 polegadas.',
                'preco_extra' => 15.00
            ],
        ];

        // 2. Itera e insere os dados
        foreach ($items as $item) {
            $this->insert($this->tableName, $item);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // Remove os dados inseridos em ordem inversa para o rollback
        $this->delete($this->tableName, ['nome_item' => 'Tela Grande']);
        $this->delete($this->tableName, ['nome_item' => 'Microfone Profissional']);
        $this->delete($this->tableName, ['nome_item' => 'Webcam Profissional']);
        $this->delete($this->tableName, ['nome_item' => 'Projetor HD']);
    }
}
