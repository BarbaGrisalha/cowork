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
 */
class Reservation extends \yii\db\ActiveRecord
{
    // 🚀 CONSTANTES DE STATUS DE NEGÓCIO (Adicione aqui)
    public const STATUS_DRAFT = 'rascunho';
    public const STATUS_PENDING = 'pendente';
    public const STATUS_PAID = 'pago'; // Usada no seu Service!
    public const STATUS_CANCELED = 'cancelado';

    public $periodo;


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
            [['status'], 'default', 'value' => self::STATUS_PENDING],

            [['customer_id', 'room_id'], 'required'],
            [['customer_id', 'room_id'], 'integer'],

            // periodo (hora, dia, mes)
            [['periodo'], 'string'],
            [['periodo'], 'default', 'value' => 'hora'],
            [['periodo'], 'in', 'range' => ['hora', 'dia', 'mes']],

            // tipo_reserva (hora, diaria, mensal)
            [['tipo_reserva'], 'string', 'max' => 20],
            [['tipo_reserva'], 'default', 'value' => 'hora'],

            // Campos que realmente existem no banco
            [['data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada'], 'safe'],

            // Só exige horário completo quando for reserva por hora
            [
                ['data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada'],
                'required',
                'when' => fn($model) => $model->periodo === 'hora'
            ],

            // Só exige data_reserva quando for diária ou mensal
            [
                ['data_reserva'],
                'required',
                'when' => fn($model) => in_array($model->periodo, ['dia', 'mes'])
            ],

            [['hora_inicio_agendada'], 'validateNotInPast'],

            [['total_estimado'], 'number'],
            [['status'], 'string', 'max' => 30],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_PAID, self::STATUS_CANCELED]],

            // Relacionamentos corretos
            [['customer_id'], 'exist', 'targetClass' => Customers::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['room_id'],     'exist', 'targetClass' => Rooms::class,      'targetAttribute' => ['room_id' => 'id']],
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
    // 1. Validador para reservas de dia/mês inteiro
    public function validateFullDayConflict($attribute, $params)
    {
        if ($this->hasErrors()) return;

        $start = $this->data_inicio;
        $end   = $this->data_fim;

        $conflict = static::find()
            ->where(['room_id' => $this->room_id])
            ->andWhere(['<>', 'status', self::STATUS_CANCELED])
            ->andWhere(['<', 'data_inicio', $end])
            ->andWhere(['>', 'data_fim', $start])
            ->exists();

        if ($conflict) {
            $this->addError($attribute, 'Este período já está reservado para a sala selecionada.');
        }
    }

    // 2. beforeSave() → A MÁGICA ACONTECE AQUI
    // common/models/Reservations.php

    // Nova função de validação
    public function validateNotInPast($attribute, $params)
    {
        if ($this->periodo === 'hora') {
            $inicio = new \DateTime($this->hora_inicio_agendada, new \DateTimeZone('Europe/Lisbon'));
            $agora  = new \DateTime('now', new \DateTimeZone('Europe/Lisbon'));

            if ($inicio <= $agora) {
                $this->addError($attribute, 'Não é permitido reservar horários no passado. Escolha uma data/hora futura.');
            }
        }

        // Para reservas diárias e mensais (usam data_inicio ou data_reserva)
        if (in_array($this->periodo, ['dia', 'mes'])) {
            $dataInicio = $this->data_inicio ?? $this->data_reserva;
            if ($dataInicio) {
                $data = new \DateTime($dataInicio . ' 00:00:00', new \DateTimeZone('Europe/Lisbon'));
                $hoje = new \DateTime('today', new \DateTimeZone('Europe/Lisbon'));

                if ($data < $hoje) {
                    $this->addError('data_inicio', 'Não é permitido reservar em datas passadas.');
                    $this->addError('data_reserva', 'Não é permitido reservar em datas passadas.');
                }
            }
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->periodo = $this->periodo ?: 'hora';

        // RESERVA MENSAL — usa apenas data_reserva (que existe no banco)
        if ($this->tipo_reserva === 'mensal') {
            if (empty($this->data_reserva)) {
                $this->addError('data_reserva', 'Data da reserva é obrigatória.');
                return false;
            }

            $dt = new \DateTime($this->data_reserva); // já vem como 2026-01-01

            $this->hora_inicio_agendada = $dt->format('Y-m-01 00:00:00');
            $this->hora_fim_agendada    = $dt->format('Y-m-t 23:59:59.999'); // evita duplicate key
            $this->total_estimado       = 800.00;
            $this->status               = $this->status ?: 'Pendente';

            return true;
        }

        // RESERVA DIÁRIA
        if ($this->periodo === 'dia') {
            if (empty($this->data_reserva)) {
                $this->addError('data_reserva', 'Data é obrigatória para reserva diária.');
                return false;
            }

            $this->hora_inicio_agendada = $this->data_reserva . ' 09:00:00';
            $this->hora_fim_agendada    = $this->data_reserva . ' 19:00:00';
            $this->total_estimado       = 32.00;
            $this->tipo_reserva         = 'diaria';

            return true;
        }

        // RESERVA POR HORA — nada muda
        if ($this->periodo === 'hora') {
            // se precisar, pode manter alguma lógica aqui
        }

        return true;
    }
}
