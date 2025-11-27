<?php

use yii\db\Migration;

class m251122_150652_add_tipo_reserva_to_reservations_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%reservations}}', 'tipo_reserva', $this->string(20)->defaultValue('hora')->after('status'));

        // Opcional: atualiza as reservas existentes para 'hora' (caso já tenha dados)
        $this->update('{{%reservations}}', ['tipo_reserva' => 'hora']);

        // Comentário na tabela pra ficar bonitinho
        $this->addCommentOnColumn('{{%reservations}}', 'tipo_reserva', 'Tipo da reserva: hora, diaria, mensal');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%reservations}}', 'tipo_reserva');
    }
}
