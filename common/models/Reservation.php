<?php

namespace common\models;

use Yii;
use common\models\AccessCodes;
use common\models\Customer;
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
 * @property int $customer_id
 * @property int $room_id
 * @property string $data_reserva
 * @property string $hora_inicio_agendada
 * @property string $hora_fim_agendada
 * @property float $total_estimado
 * @property string $status
 * @property string $tipo_reserva
 * @property string $reservation_code
 */
class Reservation extends \yii\db\ActiveRecord
{
    // Constantes de status
    public const STATUS_DRAFT     = 'rascunho';
    public const STATUS_PENDING   = 'pendente';
    public const STATUS_PAID      = 'pago';
    public const STATUS_CONFIRMED = 'confirmada';
    public const STATUS_CANCELED  = 'cancelada';

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

            [['customer_id', 'room_id'], 'required', 'on' => 'create'],
            [['customer_id', 'room_id'], 'integer'],

            // periodo (hora, dia, mes)
            [['periodo'], 'string'],
            [['periodo'], 'default', 'value' => 'hora'],
            [['periodo'], 'in', 'range' => ['hora', 'dia', 'mes']],

            // tipo_reserva
            [['tipo_reserva'], 'string', 'max' => 20],
            [['tipo_reserva'], 'default', 'value' => 'hora'],

            // Campos de data/hora
            [['data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada'], 'safe'],

            // Obrigatórios dependendo do tipo
            [
                ['data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada'],
                'required',
                'when' => fn($model) => $model->periodo === 'hora'
            ],
            [
                ['data_reserva'],
                'required',
                'when' => fn($model) => in_array($model->periodo, ['dia', 'mes'])
            ],

            [['hora_inicio_agendada'], 'validateNotInPast'],

            [['total_estimado'], 'number'],
            [['status'], 'string', 'max' => 30],
            [['status'], 'in', 'range' => [
                self::STATUS_DRAFT,
                self::STATUS_PENDING,
                self::STATUS_PAID,
                self::STATUS_CONFIRMED,
                self::STATUS_CANCELED
            ]],

            // Relações
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['room_id'],     'exist', 'skipOnError' => true, 'targetClass' => Rooms::class,      'targetAttribute' => ['room_id' => 'id']],
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
            'tipo_reserva' => 'Tipo Reserva',
            'reservation_code' => 'Código Reserva',
        ];
    }

    // Relações (mantidas iguais)
    public function getAccessCodes()
    {
        return $this->hasMany(AccessCodes::class, ['reservation_id' => 'id']);
    }
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
    public function getHorariosControles()
    {
        return $this->hasMany(HorariosControle::class, ['reservation_id' => 'id']);
    }
    public function getInvoices()
    {
        return $this->hasMany(Invoices::class, ['reservation_id' => 'id']);
    }
    public function getItems()
    {
        return $this->hasMany(Economato::class, ['id' => 'item_id'])->viaTable('reservation_items', ['reservation_id' => 'id']);
    }
    public function getItems0()
    {
        return $this->hasMany(RoomItems::class, ['id' => 'item_id'])->viaTable('reservation_room_items', ['reservation_id' => 'id']);
    }
    public function getPayments()
    {
        return $this->hasMany(Payment::class, ['reservation_id' => 'id']);
    }
    public function getReservationItems()
    {
        return $this->hasMany(ReservationItems::class, ['reservation_id' => 'id']);
    }
    public function getReservationRoomItems()
    {
        return $this->hasMany(ReservationRoomItems::class, ['reservation_id' => 'id']);
    }
    public function getRoom()
    {
        return $this->hasOne(Rooms::class, ['id' => 'room_id']);
    }

    /**
     * Valida se o início da reserva não está no passado
     */
    public function validateNotInPast($attribute, $params)
    {
        if ($this->hasErrors() || empty($this->periodo)) {
            return;
        }

        $timezone = new \DateTimeZone('Europe/Lisbon');
        $agora = new \DateTime('now', $timezone);
        $agora->modify('+5 minutes'); // tolerância de 5 minutos

        if ($this->periodo === 'hora') {
            Yii::info("=== VALIDATE NOT IN PAST ===", __METHOD__);
            Yii::info("data_reserva         = " . var_export($this->data_reserva, true), __METHOD__);
            Yii::info("hora_inicio_agendada = " . var_export($this->hora_inicio_agendada, true), __METHOD__);
            if (empty($this->hora_inicio_agendada)) {
                return;
            }

            if (empty($this->data_reserva)) {
                $this->addError('data_reserva', 'Data da reserva é obrigatória para reservas por hora.');
                return;
            }

            try {
                // Combina data + hora corretamente para evitar que DateTime use a data atual
                $dataHora = trim($this->data_reserva) . ' ' . trim($this->hora_inicio_agendada) . ':00';
                Yii::info("String montada para DateTime: " . $dataHora, __METHOD__);
                $inicio = new \DateTime($dataHora, $timezone);

                if ($inicio < $agora) {
                    $this->addError('hora_inicio_agendada', 'Não é permitido reservar horários passados.');
                    // Opcional: pode ativar se quiser destacar a data também
                    // $this->addError('data_reserva', 'Data/horário já passou.');
                }
            } catch (\Exception $e) {
                $this->addError('hora_inicio_agendada', 'Formato de horário ou data inválido.');
                $this->addError('data_reserva', 'Formato de data inválido.');
            }
        } elseif (in_array($this->periodo, ['dia', 'mes'])) {
            $data = $this->data_reserva ?? null;
            if (empty($data)) {
                return;
            }

            try {
                $inicioDia = new \DateTime($data . ' 00:00:00', $timezone);
                $hoje = new \DateTime('today', $timezone);

                if ($inicioDia < $hoje) {
                    $this->addError('data_reserva', 'Não é permitido reservar em datas passadas.');
                }
            } catch (\Exception $e) {
                $this->addError('data_reserva', 'Formato de data inválido.');
            }
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->periodo = $this->periodo ?: 'hora';

        if ($this->periodo === 'hora') {
            if (empty($this->data_reserva)) {
                $this->addError('data_reserva', 'Data da reserva é obrigatória.');
                return false;
            }

            $data = trim($this->data_reserva);

            // Converter para datetime completo
            if (!empty($this->hora_inicio_agendada)) {
                $hora = trim($this->hora_inicio_agendada);
                if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
                    $this->hora_inicio_agendada = $data . ' ' . $hora . ':00';
                }
            }

            if (!empty($this->hora_fim_agendada)) {
                $hora = trim($this->hora_fim_agendada);
                if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
                    $this->hora_fim_agendada = $data . ' ' . $hora . ':00';
                }
            }

            // CÁLCULO DO VALOR POR HORA
            if (!empty($this->hora_inicio_agendada) && !empty($this->hora_fim_agendada)) {
                try {
                    $inicio = new \DateTime($this->hora_inicio_agendada);
                    $fim    = new \DateTime($this->hora_fim_agendada);

                    // Diferença em horas (com decimais)
                    $intervalo = $inicio->diff($fim);
                    $horas     = $intervalo->h + ($intervalo->i / 60) + ($intervalo->s / 3600);

                    // Preço fixo por hora
                    $precoPorHora = 7.00;

                    // Total (pode arredondar para cima se quiser cobrar hora cheia)
                    // Opção 1: valor exato (com decimais)
                    $this->total_estimado = round($horas * $precoPorHora, 2);

                    // Opção 2: arredondar para cima (hora iniciada = hora cobrada)
                    // $this->total_estimado = ceil($horas) * $precoPorHora;

                    Yii::info("Cálculo total_estimado: {$horas} horas × {$precoPorHora}€ = {$this->total_estimado}€", __METHOD__);
                } catch (\Exception $e) {
                    Yii::error("Erro ao calcular total_estimado: " . $e->getMessage(), __METHOD__);
                    $this->total_estimado = 0.00;
                }
            } else {
                $this->total_estimado = 0.00;
            }

            $this->tipo_reserva = 'hora';
        }

        // Reserva por dia (mantém fixo)
        if ($this->periodo === 'dia' && $this->data_reserva) {
            $this->hora_inicio_agendada = $this->data_reserva . ' 09:00:00';
            $this->hora_fim_agendada    = $this->data_reserva . ' 19:00:00';
            $this->total_estimado       = 32.00;
            $this->tipo_reserva         = 'diaria';
        }

        // Reserva mensal (mantém fixo)
        if ($this->tipo_reserva === 'mensal') {
            if (empty($this->data_reserva)) {
                $this->addError('data_reserva', 'Data da reserva mensal é obrigatória.');
                return false;
            }

            $dt = new \DateTime($this->data_reserva);
            $this->hora_inicio_agendada = $dt->format('Y-m-01 00:00:00');
            $this->hora_fim_agendada    = $dt->format('Y-m-t 23:59:59');
            $this->total_estimado       = 800.00;
            $this->status               = $this->status ?: self::STATUS_PENDING;
        }

        // Debug final
        Yii::info("beforeSave FINAL - total_estimado: " . $this->total_estimado, __METHOD__);
        Yii::info("beforeSave FINAL - hora_inicio_agendada: " . $this->hora_inicio_agendada, __METHOD__);

        return true;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['update'] = ['status', 'data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada', 'tipo_reserva', 'total_estimado'];
        return $scenarios;
    }

    public function hasPaidPayment()
    {
        return Payment::find()
            ->where(['reservation_id' => $this->id])
            ->andWhere(['status' => 'aprovado'])
            ->exists();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && empty($this->reservation_code)) {
            $room = $this->room;
            $roomCode = 'SALA';
            if ($room && !empty($room->nome_sala)) {
                $roomCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', $room->nome_sala));
            }

            try {
                $dateObj = new \DateTime($this->hora_inicio_agendada);
                $dateCode = $dateObj->format('Ymd');
            } catch (\Exception $e) {
                $dateCode = date('Ymd');
            }

            $code = "RES-{$dateCode}-{$roomCode}-{$this->id}";

            // Salva apenas o campo (evita loop infinito)
            $this->updateAttributes(['reservation_code' => $code]);

            // Log para confirmar que gerou
            Yii::info("reservation_code gerado: {$code} para ID {$this->id}", __METHOD__);
        }
    }
}
