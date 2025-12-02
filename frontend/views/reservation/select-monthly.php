<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\Reservations */
/* @var $room common\models\Rooms */

$this->title = 'Reserva Mensal';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

// Bloqueia meses passados (a partir do mês atual)
$mesAtual = date('Y-m');
$js = <<<JS
$(function() {
    // Cria um calendário só de mês/ano
    $("#monthly-picker").datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'yy-mm',
        minDate: '$mesAtual-01',  // primeiro dia do mês atual
        maxDate: '+12M',
        onClose: function(dateText, inst) {
            if (dateText) {
                var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).val(year + '-' + (parseInt(month)+1));
            }
        },
        beforeShow: function(input, inst) {
            setTimeout(function() {
                inst.dpDiv.css({
                    top: $("#monthly-picker").offset().top + 40,
                    left: $("#monthly-picker").offset().left
                });
            });
            // Esconde os dias
            $('#ui-datepicker-div .ui-datepicker-calendar').hide();
            $('#ui-datepicker-div .ui-datepicker-current-day').removeClass('ui-datepicker-current-day');
        }
    });

    // Formata bonito ao carregar
    if ($("#monthly-picker").val()) {
        var v = $("#monthly-picker").val();
        var partes = v.split('-');
        if (partes.length === 2) {
            var mes = parseInt(partes[1]) - 1;
            var ano = partes[0];
            $("#monthly-picker").datepicker('setDate', new Date(ano, mes, 1));
        }
    }
});
JS;
$this->registerJs($js);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h2 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Reserva Mensal - <?= Html::encode($room->nome_sala ?? 'Sala') ?>
                    </h2>
                </div>

                <div class="card-body p-5">

                    <div class="text-center mb-5">
                        <h4>Selecione o mês que deseja reservar</h4>
                        <p class="text-muted">Acesso ilimitado 24h durante todo o mês</p>
                        <h3 class="text-success fw-bold">R$ 225,00 / mês</h3>
                    </div>

                    <?php $form = ActiveForm::begin([
                        'action' => ['reservation/checkout-monthly'],
                        'method' => 'post',
                    ]); ?>

                    <?= Html::hiddenInput('room_id', $room->id) ?>

                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold">Mês desejado</label>
                                <input type="text"
                                    id="monthly-picker"
                                    name="data_inicio"
                                    class="form-control form-control-lg text-center"
                                    placeholder="Clique para escolher o mês"
                                    readonly
                                    value="<?= date('Y-m') ?>"
                                    required>
                                <div class="form-text">Não é possível reservar meses passados</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        <?= Html::submitButton('Continuar para Pagamento', [
                            'class' => 'btn btn-success btn-lg px-5'
                        ]) ?>
                        <?= Html::a('Cancelar', ['/dashboard/index'], [
                            'class' => 'btn btn-outline-secondary btn-lg px-5 ms-3'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #ui-datepicker-div {
        font-size: 1.1em;
    }

    #ui-datepicker-div .ui-datepicker-month,
    #ui-datepicker-div .ui-datepicker-year {
        font-size: 1.2em;
        font-weight: bold;
    }
</style>