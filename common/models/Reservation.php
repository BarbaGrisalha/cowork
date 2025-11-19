<?php

namespace common\models;

use Yii;

// 🚀 IMPORTS ADICIONAIS PARA AS RELAÇÕES
use common\models\AccessCodes;
use common\models\Customers;
use common\models\HorariosControle;
use common\models\Invoices;
use common\models\Economato;
use common\models\RoomItems;
use common\models\Payment;
use common\models\ReservationItems;
use common\models\ReservationRoomItems;
use common\models\Rooms;


/**
 * This is the model class for table "reservations".
 *
 * @property int $id
// ... (restante dos PHPDocs)
 */
class Reservation extends \yii\db\ActiveRecord
{
    // 🚀 CONSTANTES DE STATUS DE NEGÓCIO (Adicione aqui)
    public const STATUS_DRAFT = 'rascunho';
    public const STATUS_PENDING = 'pendente';
    public const STATUS_PAID = 'pago'; // Usada no seu Service!
    public const STATUS_CANCELED = 'cancelado';


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reservations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['total_estimado'], 'default', 'value' => 0.00],
            // 💡 Usando a constante para o valor default
            [['status'], 'default', 'value' => self::STATUS_PENDING],

            [['customer_id', 'room_id', 'hora_inicio_agendada', 'hora_fim_agendada'], 'required'],
            [['customer_id', 'room_id'], 'integer'],
            [['data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada'], 'safe'],
            [['total_estimado'], 'number'],
            [['status'], 'string', 'max' => 30],

            // 🚀 NOVA REGRA: Garante que o status é um dos valores definidos (Enum/Range)
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_PAID, self::STATUS_CANCELED]],

            //[['room_id', 'hora_inicio_agendada', 'hora_fim_agendada'], 'unique', 'targetAttribute' => ['room_id', 'hora_inicio_agendada', 'hora_fim_agendada']],
            // 🚀 REGRA OBRIGATÓRIA 1: Garante que a data é futura ou igual à de hoje
            ['data_reserva', 'date', 'min' => date('Y-m-d'), 'tooSmall' => 'Não é possível agendar para datas passadas.'],

            // 🚀 REGRA OBRIGATÓRIA 2: Garante que a hora de fim é posterior à hora de início
            ['hora_fim_agendada', 'compare', 'compareAttribute' => 'hora_inicio_agendada', 'operator' => '>', 'type' => 'time', 'message' => 'A hora de fim deve ser posterior à hora de início.'],

            // 🚀 REGRA MESTRA: Validador customizado para checar sobreposição de horários
            [['room_id', 'data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada'], 'validateReservationConflict'],

            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customers::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rooms::class, 'targetAttribute' => ['room_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'room_id' => 'Room ID',
            'data_reserva' => 'Data Reserva',
            'hora_inicio_agendada' => 'Hora Inicio Agendada',
            'hora_fim_agendada' => 'Hora Fim Agendada',
            'total_estimado' => 'Total Estimado',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[AccessCodes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessCodes()
    {
        return $this->hasMany(AccessCodes::class, ['reservation_id' => 'id']);
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customers::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[HorariosControles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHorariosControles()
    {
        return $this->hasMany(HorariosControle::class, ['reservation_id' => 'id']);
    }

    /**
     * Gets query for [[Invoices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoices::class, ['reservation_id' => 'id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Economato::class, ['id' => 'item_id'])->viaTable('reservation_items', ['reservation_id' => 'id']);
    }

    /**
     * Gets query for [[Items0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems0()
    {
        return $this->hasMany(RoomItems::class, ['id' => 'item_id'])->viaTable('reservation_room_items', ['reservation_id' => 'id']);
    }

    /**
     * Gets query for [[Payments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payment::class, ['reservation_id' => 'id']);
    }

    /**
     * Gets query for [[ReservationItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservationItems()
    {
        return $this->hasMany(ReservationItems::class, ['reservation_id' => 'id']);
    }

    /**
     * Gets query for [[ReservationRoomItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservationRoomItems()
    {
        return $this->hasMany(ReservationRoomItems::class, ['reservation_id' => 'id']);
    }

    /**
     * Gets query for [[Room]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Rooms::class, ['id' => 'room_id']);
    }

    /**
     * Valida se o período de agendamento não entra em conflito com reservas existentes.
     * * @param string $attribute the attribute currently being validated (ignored)
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateReservationConflict($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $query = self::find()
                ->where([
                    'room_id' => $this->room_id,
                    'data_reserva' => $this->data_reserva,
                ])
                // Exclui a reserva atual se for um update (senão daria conflito com ela mesma)
                ->andFilterWhere(['<>', 'id', $this->id])
                ->andWhere([
                    'OR',
                    // 1. O novo agendamento começa ANTES e termina DEPOIS de um agendamento existente (sobreposição total)
                    [
                        'AND',
                        ['<=', 'hora_inicio_agendada', $this->hora_inicio_agendada],
                        ['>', 'hora_fim_agendada', $this->hora_inicio_agendada]
                    ],
                    // 2. O novo agendamento começa DENTRO de um agendamento existente
                    [
                        'AND',
                        ['<', 'hora_inicio_agendada', $this->hora_fim_agendada],
                        ['>=', 'hora_fim_agendada', $this->hora_fim_agendada]
                    ],
                    // 3. O novo agendamento envolve COMPLETAMENTE um agendamento existente
                    [
                        'AND',
                        ['>=', 'hora_inicio_agendada', $this->hora_inicio_agendada],
                        ['<=', 'hora_fim_agendada', $this->hora_fim_agendada]
                    ]
                ]);

            // Se encontrou algum conflito...
            if ($query->exists()) {
                $this->addError('hora_inicio_agendada', 'O horário de agendamento selecionado está em conflito com uma reserva existente nesta sala e data.');
                $this->addError('hora_fim_agendada', 'Conflito de horário detectado.');
            }
        }
    }
}
