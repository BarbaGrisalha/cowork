<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model frontend\models\Reservations */
/* @var $room frontend\models\Room | null */ // CRÍTICO: O Controller deve passar este objeto!

$this->title = 'Agendar Espaço';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="reservation-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($room): ?>
        <p class="lead">Agendando: **<?= Html::encode($room->nome_sala) ?>**</p>
    <?php else: ?>
        <p class="lead text-danger">Erro: Nenhuma sala selecionada ou sala não encontrada.</p>
    <?php endif; ?>

    <div class="reservation-form">

        <?php $form = ActiveForm::begin(); ?>

        <?php
        // LÓGICA CORRIGIDA:
        // Se o room_id veio, ele já está preenchido e deve ser oculto.
        if ($model->room_id):
            // O ID é enviado, mas o campo fica invisível.
            echo $form->field($model, 'room_id')->hiddenInput()->label(false);
        else:
            // Caso contrário, você pode mostrar um campo de texto desabilitado (apenas se for para Debug).
            // Idealmente, se o ID não veio, o Controller deveria redirecionar.
            echo $form->field($model, 'room_id')->textInput(['disabled' => true, 'value' => 'Selecione a Sala']);
        endif;
        ?>

        <?= $form->field($model, 'data_reserva')->widget(DatePicker::class, [
            'dateFormat' => 'yyyy-MM-dd',
            'options' => ['class' => 'form-control'],
        ]) ?>

        <?= $form->field($model, 'hora_inicio_agendada')->widget(MaskedInput::class, [
            'mask' => '99:99:99',
            'options' => ['placeholder' => 'HH:MM:SS', 'class' => 'form-control'],
        ]) ?>

        <?= $form->field($model, 'hora_fim_agendada')->widget(MaskedInput::class, [
            'mask' => '99:99:99',
            'options' => ['placeholder' => 'HH:MM:SS', 'class' => 'form-control'],
        ]) ?>

        <?= $form->field($model, 'status')->hiddenInput(['value' => 'pendente'])->label(false) ?>

        <div class="form-group mt-3">
            <?= Html::submitButton('Confirmar Reserva', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>