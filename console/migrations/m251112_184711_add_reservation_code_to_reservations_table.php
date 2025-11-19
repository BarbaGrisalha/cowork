<?php

use yii\db\Migration;

class m251112_184711_add_reservation_code_to_reservations_table extends Migration
{
   /**
     * {@inheritdoc}
     * Usamos safeUp para garantir que tudo corre dentro de uma transação.
     */
    public function safeUp()
    {
        Yii::info('Adicionando coluna reservation_code à tabela reservations', __METHOD__);

        // 1. Adicionar a coluna.
        // O ->null()->defaultValue(null) é CRÍTICO.
        // Precisamos que o 'INSERT' inicial funcione ANTES do 'afterSave'
        // gerar o código.
        $this->addColumn(
            '{{%reservations}}', // {{%...}} usa o prefixo da tabela, se houver
            'reservation_code',
            $this->string(100)->null()->defaultValue(null)->comment('Código único da reserva gerado internamente')
        );

        // 2. Adicionar o índice UNIQUE.
        // Isto é o que garante que não há códigos duplicados,
        // e também acelera as pesquisas por este campo.
        $this->createIndex(
            'uk_reservations_reservation_code', // Nome do índice (uk = unique key)
            '{{%reservations}}', // Nome da tabela
            'reservation_code',   // Coluna(s)
            true                  // true = este é um índice UNIQUE
        );
    }

    /**
     * {@inheritdoc}
     * O safeDown reverte o que o safeUp fez.
     */
    public function safeDown()
    {
        Yii::info('Revertendo a adição da coluna reservation_code', __METHOD__);

        // Revertemos na ordem OPOSTA da criação

        // 1. Remover o índice
        $this->dropIndex(
            'uk_reservations_reservation_code',
            '{{%reservations}}'
        );

        // 2. Remover a coluna
        $this->dropColumn('{{%reservations}}', 'reservation_code');
    }
}
