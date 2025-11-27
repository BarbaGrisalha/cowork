<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $room common\models\Rooms */
/* @var $reservedDates array */

$this->title = 'Reserva Diária - ' . Html::encode($room->nome_sala);
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-success text-white text-center py-4 rounded-top-4">
                    <h2 class="mb-0">
                        Reserva Diária<br>
                        <small class="fs-5"><?= Html::encode($room->nome_sala) ?></small>
                    </h2>
                </div>

                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="<?= Yii::getAlias('@web') ?>/uploads/rooms/1.jpeg?v=<?= time() ?>"
                            class="img-fluid rounded-3 shadow-sm" style="max-height: 220px;" alt="<?= $room->nome_sala ?>">
                    </div>

                    <div class="alert alert-success text-center">
                        <h4><strong>R$ 32,00</strong> por dia</h4>
                        <p class="mb-0">Das 09:00 às 19:00 – uso completo da sala</p>
                    </div>

                    <?php $form = ActiveForm::begin([
                        'action' => ['/payment/checkout-daily'],
                        'method' => 'post',
                        'id' => 'daily-form'
                    ]); ?>

                    <?= Html::hiddenInput('room_id', $room->id) ?>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Selecione a data desejada:</label>
                        <input type="date" name="date" class="form-control form-control-lg text-center"
                            min="<?= date('Y-m-d') ?>" required
                            onchange="checkDate(this.value, <?= $room->id ?>)">
                    </div>

                    <div id="date-warning" class="alert alert-danger d-none">
                        Esta data já está reservada!
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow">
                            Reservar este dia → Ir para pagamento
                        </button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script simples pra bloquear datas já reservadas -->
<script>
    const reservedDates = <?= json_encode($reservedDates) ?>;

    function checkDate(selectedDate, roomId) {
        const warning = document.getElementById('date-warning');
        const button = document.querySelector('#daily-form button[type="submit"]');

        if (reservedDates.includes(selectedDate)) {
            warning.classList.remove('d-none');
            button.disabled = true;
            button.innerHTML = 'Data Indisponível';
            button.classList.replace('btn-success', 'btn-secondary');
        } else {
            warning.classList.add('d-none');
            button.disabled = false;
            button.innerHTML = 'Reservar este dia → Ir para pagamento';
            button.classList.replace('btn-secondary', 'btn-success');
        }
    }

    // Bloqueia sábado e domingo automaticamente
    document.querySelector('input[type="date"]').addEventListener('input', function(e) {
        const day = new Date(e.target.value).getDay();
        if ([0, 6].includes(day)) {
            e.target.setCustomValidity('Fim de semana não disponível');
        } else {
            e.target.setCustomValidity('');
        }
    });
</script>