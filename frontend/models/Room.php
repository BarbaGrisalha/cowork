<?php

namespace frontend\models;

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
 * @property PricingPlans $pricingPlan
 * @property Reservations[] $reservations
 * @property float $preco_hora
 * @property float $preco_dia
 * @property float $preco_mes
 */
class Room extends \yii\db\ActiveRecord
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
            [['pricing_plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => PricingPlans::class, 'targetAttribute' => ['pricing_plan_id' => 'id']],
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
            'descricao' => 'Descricao',
            'pricing_plan_id' => 'Pricing Plan ID',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[PricingPlan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPricingPlan()
    {
        return $this->hasOne(PricingPlans::class, ['id' => 'pricing_plan_id']);
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
