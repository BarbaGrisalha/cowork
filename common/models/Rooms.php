<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "rooms".
 *
 * @property int $id
 * @property string $nome_sala
 * @property int $capacidade
 * @property string|null $descricao
 * @property int|null $pricing_plan_id
 * @property string $status
 *
 * @property Reservations[] $reservations
 */
class Rooms extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rooms';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descricao', 'pricing_plan_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'ativa'],
            [['nome_sala', 'capacidade'], 'required'],
            [['capacidade', 'pricing_plan_id'], 'integer'],
            [['descricao'], 'string'],
            [['nome_sala'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 30],
            // Removido validator para pricing_plan_id (classe inexistente)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome_sala' => 'Nome Sala',
            'capacidade' => 'Capacidade',
            'descricao' => 'Descrição',
            'pricing_plan_id' => 'Plano de Preços ID',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[Reservations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservations()
    {
        return $this->hasMany(Reservations::class, ['room_id' => 'id']);
    }
}
