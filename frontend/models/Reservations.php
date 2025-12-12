<?php

//namespace app\models; // A sua namespace original está correta
namespace frontend\models;

use Yii;
use frontend\models\Room;     // Verifique se este namespace está correto
use frontend\models\Customer; // Verifique se este namespace está correto
use DateTime; // <<< ADICIONADO: Necessário para lógica de datas
use yii\yii\web\NotFoundHttpException;

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
 * @property string|null $reservation_code // <<< ADICIONADO: Propriedade para o código
 *
 * @property AccessCodes[] $accessCodes
 * @property Customers $customer
 * @property HorariosControle[] $horariosControles
 * @property Invoices[] $invoices
 * @property Economato[] $items
 * @property RoomItems[] $items0
 * @property Payments[] $payments
 * @property ReservationItems[] $reservationItems
 * @property ReservationRoomItems[] $reservationRoomItems
 * @property Rooms $room
 */
class Reservations extends \yii\db\ActiveRecord
{
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
            [['status'], 'default', 'value' => 'pendente'],

            // CORRIGIDO: Usando nomes de colunas do DB (booking_start_time, booking_end_time)
            [['customer_id', 'room_id', 'hora_inicio_agendada', 'hora_fim_agendada'], 'required'],

            [['customer_id', 'room_id'], 'integer'],

            // CORRIGIDO: Usando nomes de colunas do DB (booking_date, etc.)
            [['data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada'], 'safe'],

            [['total_estimado'], 'number'],
            [['status'], 'string', 'max' => 30],

            // <<< ADICIONADO: Validação customizada para sobreposição
            // CORRIGIDO: Chamando o validador com o nome da coluna real
            ['hora_inicio_agendada', 'validateOverlap'],

            // <<< CORRIGIDO: O targetClass deve ser 'Customer::class' (singular), como nos seus 'use' e 'get'
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],

            // <<< CORRIGIDO: O targetClass deve ser 'Room::class' (singular)
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::class, 'targetAttribute' => ['room_id' => 'id']],

            // <<< ADICIONADO: Regras para o novo código de reserva
            [['reservation_code'], 'string', 'max' => 50],
            [['reservation_code'], 'unique'],
            [['tipo_reserva'], 'string', 'max' => 20],
            [['tipo_reserva'], 'default', 'value' => 'hora'],
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
            'reservation_code' => 'Código da Reserva', // <<< ADICIONADO
        ];
    }

    // --- MÉTODOS DE VALIDAÇÃO CUSTOMIZADA ---

    /**
     * Validador customizado para impedir sobreposição de reservas (overlap).
     * Isto é o que impede double-booking.
     *
     * @param string $attribute O atributo sendo validado (hora_inicio_agendada)
     */
    public function validateOverlap($attribute, $params)
    {
        if ($this->hasErrors()) {
            // Não vamos validar se outros erros (ex: 'required') já falharam
            return;
        }

        // Datas da *nova* reserva
        $newStart = $this->hora_inicio_agendada;
        $newEnd = $this->hora_fim_agendada;

        // Garantir que a data final é depois da inicial
        if ($newStart >= $newEnd) {
            $this->addError($attribute, 'A hora de fim deve ser posterior à hora de início.');
            return;
        }

        // A lógica de sobreposição:
        // Encontrar qualquer reserva na MESMA SALA onde:
        // O início (existente) é ANTES do fim (novo) E
        // O fim (existente) é DEPOIS do início (novo)
        $query = Reservations::find()
            ->where(['room_id' => $this->room_id])
            ->andWhere(['<', 'hora_inicio_agendada', $newEnd])
            ->andWhere(['>', 'hora_fim_agendada', $newStart])
            ->andWhere(['>=', 'hora_fim_agendada', date('Y-m-d H:i:s')]);

        // Se estivermos a *atualizar* uma reserva (isNewRecord é false),
        // temos que excluir o ID dela da verificação,
        // senão ela vai conflitar consigo mesma.
        if (!$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        // Se a query retornar *qualquer coisa*, temos um conflito.
        if ($query->exists()) {
            $this->addError($attribute, 'Este horário está em conflito com uma reserva existente para esta sala.');
            $this->addError('hora_fim_agendada', 'Conflito de horário detectado.');
        }
    }

    // --- MÉTODOS DE EVENTOS (HOOKS) ---

    /**
     * afterSave: Disparado após salvar (criar ou atualizar)
     * Usamos isto para gerar o código da reserva após o 'id' existir.
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Só gera o código na criação (insert) e se ainda não tiver vazio
        if ($insert && empty($this->reservation_code)) {

            // Pega o nome da sala
            $room = $this->room; // já tem relação getRoom()
            $roomCode = 'SALA';
            if ($room && !empty($room->nome_sala)) {
                $roomCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', $room->nome_sala));
            }

            // Usa a data_inicio_agendada para pegar a data (funciona para hora, diária e mensal)
            $dateObj = new \DateTime($this->hora_inicio_agendada);
            $dateCode = $dateObj->format('Ymd'); // 20260101

            // Monta o código: RES-AAAAMMDD-SALAXX-ID
            $code = "RES-{$dateCode}-{$roomCode}-{$this->id}";

            // ← ex: RES-20260101-MESA1-85

            // Salva sem disparar afterSave de novo (evita loop)
            $this->updateAttributes(['reservation_code' => $code]);
        }
    }


    // --- MÉTODOS DE RELACIONAMENTO (GETTERS) ---
    // (O seu código original - parecem corretos, assumindo que as classes existem)

    /**
     * Gets query for [[AccessCodes]].
     * @return \yii\db\ActiveQuery
     */
    public function getAccessCodes()
    {
        return $this->hasMany(AccessCodes::class, ['reservation_id' => 'id']); //linha com erro - 192
    }

    /**
     * Gets query for [[Customer]].
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[HorariosControles]].
     * @return \yii\db\ActiveQuery
     */
    public function getHorariosControles()
    {
        return $this->hasMany(HorariosControle::class, ['reservation_id' => 'id']); //linha com erro - 210
    }

    /**
     * Gets query for [[Invoices]].
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoices::class, ['reservation_id' => 'id']); //linha com erro - 219
    }

    /**
     * Gets query for [[Items]].
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Economato::class, ['id' => 'item_id'])->viaTable('reservation_items', ['reservation_id' => 'id']); //linha com erro - 228
    }

    /**
     * Gets query for [[Items0]].
     * @return \yii\db\ActiveQuery
     */
    public function getItems0()
    {
        return $this->hasMany(RoomItems::class, ['id' => 'item_id'])->viaTable('reservation_room_items', ['reservation_id' => 'id']); //linha com erro - 237
    }

    /**
     * Gets query for [[Payments]].
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payments::class, ['reservation_id' => 'id']); //linha com erro - 246
    }

    /**
     * Gets query for [[ReservationItems]].
     * @return \yii\db\ActiveQuery
     */
    public function getReservationItems()
    {
        return $this->hasMany(ReservationItems::class, ['reservation_id' => 'id']); ////linha com erro - 255
    }

    /**
     * Gets query for [[ReservationRoomItems]].
     * @return \yii\db\ActiveQuery
     */
    public function getReservationRoomItems()
    {
        return $this->hasMany(ReservationRoomItems::class, ['reservation_id' => 'id']); //linha com erro - 264
    }

    /**
     * Gets query for [[Room]].
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Room::class, ['id' => 'room_id']);
    }

    // --- FORMATAÇÃO DE SAÍDA (API) ---

    /**
     * fields(): Controla quais campos são retornados pela API.
     * O seu código original aqui estava bom.
     */
    // DENTRO da classe Reservations
    public function fields()
    {
        $fields = parent::fields();

        // ... (campos existentes)
        $fields['reservation_code'] = 'reservation_code';

        // O SEU TÍTULO (MANTIDO)
        $fields['title'] = function ($model) {
            $roomName = $model->room->nome_sala ?? 'Mesa Indefinida';
            $customerName = $model->customer->nome ?? 'Cliente Desconhecido';
            return "Reserva #{$model->id}: {$roomName} ({$customerName})";
        };

        // 🚀 CORREÇÃO CRÍTICA PARA A API: Concatenação na SAÍDA
        $fields['start'] = function ($model) {
            // Concatena as colunas separadas do DB (DATE + TIME) em um único DATETIME para o calendário
            return $model->data_reserva . ' ' . $model->hora_inicio_agendada;
        };

        // 🚀 CORREÇÃO CRÍTICA PARA A API: Concatenação na SAÍDA
        $fields['end'] = function ($model) {
            // Concatena as colunas separadas do DB (DATE + TIME) em um único DATETIME para o calendário
            return $model->data_reserva . ' ' . $model->hora_fim_agendada;
        };

        // ... (cor do status mantido)

        return $fields;
    }

    public function beforeValidate()
    {
        // A Lógica de Concatenação SÓ DEVE RODAR NA CRIAÇÃO DE NOVOS REGISTROS.
        if ($this->isNewRecord) {

            // Esta é a lógica que transforma "2025-11-16" e "11:00:00" em um DATETIME completo.
            if (!empty($this->data_reserva) && !empty($this->hora_inicio_agendada) && !empty($this->hora_fim_agendada)) {
                $this->hora_inicio_agendada = $this->data_reserva . ' ' . $this->hora_inicio_agendada;
                $this->hora_fim_agendada = $this->data_reserva . ' ' . $this->hora_fim_agendada;
            }
        }
        // Se isNewRecord for FALSE (ou seja, UPDATE/Cancelamento), a lógica acima é ignorada.

        return parent::beforeValidate();
    }

    /**
     * beforeSave: Usado para manipulação final de dados, como o cálculo do preço.
     * @param bool $insert
     * @return bool
     * beforeSave: Preenche datas/horas corretas e calcula preço
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Garantir que sempre tenha um valor
        $this->tipo_reserva = $this->tipo_reserva ?: 'hora';

        // RESERVA MENSAL — 100% compatível com seu banco atual
        if ($this->tipo_reserva === 'mensal') {
            // Recebemos data_reserva no formato 2026-01-01 (vem do hidden input)
            if (empty($this->data_reserva)) {
                $this->addError('data_reserva', 'Data da reserva mensal é obrigatória.');
                return false;
            }

            $dt = new \DateTime($this->data_reserva); // já vem como 2026-01-01

            // Preenche os campos que EXISTEM no banco
            $this->hora_inicio_agendada = $dt->format('Y-m-01 00:00:00');
            $this->hora_fim_agendada    = $dt->format('Y-m-t 23:59:59.999'); // .999 evita duplicate key

            $this->total_estimado = 800.00;
            $this->status         = $this->status ?: 'Pendente';

            return true;
        }

        // RESERVA DIÁRIA
        if ($this->tipo_reserva === 'diaria') {
            if (empty($this->data_inicio)) {
                $this->addError('data_inicio', 'Data é obrigatória para reserva diária.');
                return false;
            }

            $this->hora_inicio_agendada = $this->data_inicio . ' 09:00:00';
            $this->hora_fim_agendada    = $this->data_inicio . ' 19:00:00';
            $this->total_estimado = 32.00;

            return true;
        }

        // RESERVA POR HORA → cálculo normal (seu código antigo)
        if ($this->tipo_reserva === 'hora') {
            if ($insert || $this->total_estimado == 0.00) {
                $HOURLY_RATE = 7.00;
                try {
                    $startTime = new DateTime($this->hora_inicio_agendada);
                    $endTime   = new DateTime($this->hora_fim_agendada);
                    $interval  = $startTime->diff($endTime);
                    $totalHoursDecimal = $interval->h + ($interval->i / 60);
                    $calculatedPrice = ceil($totalHoursDecimal) * $HOURLY_RATE;
                    $this->total_estimado = round($calculatedPrice, 2);
                } catch (\Exception $e) {
                    Yii::error("Erro no cálculo do preço da Reserva #{$this->id}: " . $e->getMessage());
                    $this->total_estimado = 0.00;
                }
            }
            return true;
        }

        return true;
    }
}
