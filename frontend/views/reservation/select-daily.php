<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $room common\models\Rooms */
/* @var $reservedDates array */  // <-- IMPORTANTE: deve vir como array de strings 'YYYY-MM-DD'

$this->title = 'Reserva Diária - ' . Html::encode($room->nome_sala);
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-success text-white text-center py-4">
                    <h2 class="mb-0">
                        Reserva Diária<br>
                        <small class="fs-5"><?= Html::encode($room->nome_sala) ?></small>
                    </h2>
                </div>

                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="<?= Yii::getAlias('@web') ?>/uploads/rooms/<?= $room->id ?>.jpeg?v=<?= time() ?>"
                            class="img-fluid rounded-3 shadow-sm"
                            style="max-height: 220px; object-fit: cover;"
                            alt="<?= Html::encode($room->nome_sala) ?>">
                    </div>

                    <div class="alert alert-success text-center mb-4">
                        <h4><strong>R$ 32,00</strong> por dia inteiro</h4>
                        <p class="mb-0">Das 09:00 às 19:00 – uso exclusivo da sala</p>
                    </div>

                    <?php $form = ActiveForm::begin([
                        'action' => ['/payment/checkout-daily'],
                        'method' => 'post',
                        'id' => 'daily-form'
                    ]); ?>

                    <?= Html::hiddenInput('room_id', $room->id) ?>

                    <div class="mb-4">
                        <label class="form-label fw-bold fs-5">Escolha a data:</label>
                        <input type="date"
                            name="date"
                            class="form-control form-control-lg text-center"
                            min="<?= date('Y-m-d') ?>"
                            required>
                    </div>

                    <div id="date-warning" class="alert alert-danger d-none text-center fw-bold">
                        Esta data já está reservada ou é fim de semana!
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit"
                            id="submit-btn"
                            class="btn btn-success btn-lg px-5 shadow">
                            Reservar este dia → Ir para pagamento
                        </button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Passa as datas reservadas de forma segura pro JS
$reservedJson = Json::encode($reservedDates);

// Data de hoje no formato YYYY-MM-DD
$today = date('Y-m-d');

$this->registerJs(
    <<<JS
    const reservedDates = $reservedJson;
    const today = "$today";
    const dateInput = document.querySelector('input[name="date"]');
    const warning = document.getElementById('date-warning');
    const submitBtn = document.getElementById('submit-btn');

    function validateDate() {
        const selected = dateInput.value;
        
        if (!selected) {
            warning.classList.add('d-none');
            submitBtn.disabled = true;
            return;
        }

        const selectedDate = new Date(selected);
        const dayOfWeek = selectedDate.getDay(); // 0 = domingo, 6 = sábado

        const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
        const isReserved = reservedDates.includes(selected);

        if (isWeekend || isReserved) {
            warning.classList.remove('d-none');
            warning.textContent = isWeekend 
                ? "Fim de semana não disponível para reserva" 
                : "Esta data já está reservada!";
            submitBtn.disabled = true;
            submitBtn.classList.replace('btn-success', 'btn-secondary');
            submitBtn.textContent = "Data Indisponível";
        } else {
            warning.classList.add('d-none');
            submitBtn.disabled = false;
            submitBtn.classList.replace('btn-secondary', 'btn-success');
            submitBtn.textContent = "Reservar este dia → Ir para pagamento";
        }
    }

    // Executa na carga e sempre que mudar a data
    dateInput.addEventListener('change', validateDate);
    dateInput.addEventListener('input', validateDate);

    // Validação inicial (caso já tenha valor)
    if (dateInput.value) validateDate();

    // Bloqueia seleção visual de fim de semana (opcional, mas fica bonito)
    dateInput.addEventListener('input', function(e) {
        const val = e.target.value;
        if (val) {
            const d = new Date(val);
            if ([0,6].includes(d.getDay())) {
                e.target.setCustomValidity('Fins de semana não estão disponíveis');
            } else {
                e.target.setCustomValidity('');
            }
        }
    });
JS
);
?>