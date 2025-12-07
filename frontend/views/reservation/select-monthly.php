<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Reservation;
use common\models\Customers;

/* @var $this yii\web\View */
/* @var $model common\models\Reservation */
/* @var $room common\models\Rooms */

$this->title = 'Reserva Mensal';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

// === DATA MÍNIMA: hoje + 30 dias → primeiro dia do mês permitido ===
$hoje = new \DateTime('now', new \DateTimeZone('America/Sao_Paulo'));
$hoje->modify('+30 days');
$minDateJs = $hoje->format('Y-m');                    // ex: 2026-01
$mesMinimoFormatado = $hoje->format('F/Y');           // ex: Janeiro/2026
$mesMinimoBonito = ucfirst($hoje->format('F \d\e Y')); // Janeiro de 2026

$js = <<<JS
$(function() {
    $("#monthly-picker").datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'yy-mm',
        minDate: '{$minDateJs}-01',
        maxDate: '+13M',
        onClose: function() {
            var month = parseInt($("#ui-datepicker-div .ui-datepicker-month :selected").val()) + 1;
            var year  = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            month = month < 10 ? '0' + month : month;
            var value = year + '-' + month;
            $(this).val(value);

            var date = new Date(year, month - 1, 1);
            $("#monthly-picker-display").val(date.toLocaleDateString('pt-BR', {year:'numeric', month:'long'}));
        },
        beforeShow: function() {
            setTimeout(function() {
                $('#ui-datepicker-div .ui-datepicker-calendar').hide();
            }, 10);
        }
    });

    $("#monthly-picker").val('$minDateJs');
    $("#monthly-picker-display").val("$mesMinimoBonito");
});
JS;
$this->registerJs($js);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <!-- NOVA RESERVA -->
            <div class="card border-0 shadow-lg mb-5">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h2 class="mb-0">Reserva Mensal - <?= Html::encode($room->nome_sala) ?></h2>
                </div>
                <div class="card-body p-5">

                    <div class="text-center mb-5">
                        <h4>Selecione o mês de início da reserva</h4>
                        <p class="text-muted mb-1">
                            <strong>Aviso:</strong> Só é possível iniciar uma reserva mensal <u>30 dias a partir de hoje</u>
                        </p>
                        <p class="text-primary fw-bold fs-5">
                            Primeiro mês disponível: <?= $mesMinimoBonito ?>
                        </p>
                        <h3 class="text-success fw-bold">
                            R$ <?= number_format($room->monthly_price ?? 225, 2, ',', '.') ?> / mês
                        </h3>
                    </div>

                    <?php $form = ActiveForm::begin([
                        'action' => ['reservation/checkout-monthly'],
                        'method' => 'post',
                    ]); ?>

                    <?= Html::hiddenInput('Reservations[room_id]', $room->id) ?>
                    <input type="text"
                        id="monthly-picker"
                        name="Reservations[data_reserva]"
                        value="<?= $minDateJs . '-01' ?>"
                        style="position:absolute; left:-9999px;">


                    <div class="row justify-content-center mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mês de início</label>
                            <input type="text"
                                id="monthly-picker-display"
                                class="form-control form-control-lg text-center"
                                readonly
                                style="cursor:pointer;background:white;font-size:1.4rem;"
                                value="<?= $mesMinimoBonito ?>">
                            <div class="form-text text-primary">
                                Clique para escolher outro mês (a partir de <?= $mesMinimoBonito ?>)
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <?= Html::submitButton('Continuar para Pagamento', ['class' => 'btn btn-success btn-lg px-5']) ?>
                        <?= Html::a('Voltar', ['/dashboard/index'], ['class' => 'btn btn-outline-secondary btn-lg px-5 ms-3']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <!-- MINHAS RESERVAS MENSAIS -->
            <?php
            // Pega customer_id do usuário logado
            $customerId = Yii::$app->user->isGuest
                ? null
                : Customers::findOne(['user_id' => Yii::$app->user->id])?->id;

            // Busca todas as reservas mensais do cliente
            $todasReservasMensais = Reservation::find()
                ->where([
                    'customer_id'  => $customerId,
                    'tipo_reserva' => 'mensal',
                ])
                ->andWhere(['!=', 'status', Reservation::STATUS_CANCELED])
                ->with('room')
                ->orderBy(['hora_inicio_agendada' => SORT_ASC])
                ->all();

            // Filtra no PHP: só as que ainda não começaram
            $hoje = new \DateTime('today');
            $minhasReservas = array_filter($todasReservasMensais, function ($r) use ($hoje) {
                $inicio = new \DateTime($r->hora_inicio_agendada);
                return $inicio >= $hoje;
            });
            ?>

            <?php if ($minhasReservas): ?>
                <h3 class="text-center mb-4 text-primary">
                    Minhas reservas mensais ativas / futuras
                </h3>

                <div class="row g-4">
                    <?php foreach ($minhasReservas as $reserva): ?>
                        <?php
                        $inicio = new \DateTime($reserva->hora_inicio_agendada);
                        $mesAno = $inicio->format('F/Y');
                        $badgeClass = $reserva->status === Reservation::STATUS_PAID ? 'bg-success' : ($reserva->status === Reservation::STATUS_PENDING ? 'bg-warning text-dark' : 'bg-secondary');
                        $statusTexto = $reserva->status === Reservation::STATUS_PAID ? 'Pago' : ($reserva->status === Reservation::STATUS_PENDING ? 'Pendente' : ucfirst($reserva->status));
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0 <?= $reserva->status === Reservation::STATUS_PAID ? 'border-success border-3' : '' ?>">
                                <div class="card-body text-center py-4">
                                    <h5 class="card-title fw-bold text-primary">
                                        <?= Html::encode($reserva->room->nome_sala) ?>
                                    </h5>
                                    <p class="display-6 text-dark mb-2"><?= $mesAno ?></p>

                                    <span class="badge <?= $badgeClass ?> fs-6 px-3 py-2 mb-3">
                                        <?= $statusTexto ?>
                                    </span>

                                    <p class="fs-4 fw-bold text-success mb-3">
                                        R$ <?= number_format($room->monthly_price ?? 225, 2, ',', '.') ?>
                                    </p>

                                    <?php if ($reserva->status === Reservation::STATUS_PENDING): ?>
                                        <?= Html::a('Cancelar', ['reservation/cancel-monthly', 'id' => $reserva->id], [
                                            'class' => 'btn btn-sm btn-outline-danger',
                                            'data' => [
                                                'confirm' => 'Tem certeza que deseja cancelar esta reserva mensal?',
                                                'method' => 'post',
                                            ]
                                        ]) ?>
                                    <?php else: ?>
                                        <small class="text-muted">Reserva paga — cancelamento indisponível</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light border text-center py-4">
                    <p class="mb-0">Você ainda não possui reservas mensais ativas ou futuras.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
    #ui-datepicker-div {
        font-size: 1.2em;
        z-index: 9999 !important;
    }

    #ui-datepicker-div .ui-datepicker-month,
    #ui-datepicker-div .ui-datepicker-year {
        font-weight: bold;
    }
</style>

<script>
    $(document).on('click', '#monthly-picker-display', function() {
        $("#monthly-picker").datepicker("show");
    });
</script>