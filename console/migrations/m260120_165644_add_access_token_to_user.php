<?php

use yii\db\Migration;

/**
 * Class m260120_165644_add_access_token_to_user
 * Adiciona o campo access_token à tabela user para autenticação Bearer
 */
class m260120_165644_add_access_token_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%user}}',
            'access_token',
            $this->string(255)->null()->after('auth_key')
        );

        // Opcional: adiciona índice único para buscas rápidas (recomendado)
        $this->createIndex(
            'idx-user-access_token',
            '{{%user}}',
            'access_token',
            true // unique = true
        );

        echo "Campo 'access_token' adicionado à tabela user.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove o índice primeiro (se existir)
        $this->dropIndex('idx-user-access_token', '{{%user}}');

        // Remove a coluna
        $this->dropColumn('{{%user}}', 'access_token');

        echo "Campo 'access_token' removido da tabela user.\n";

        return true;
    }
}