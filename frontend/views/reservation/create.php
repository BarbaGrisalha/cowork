<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model frontend\models\Reservations */
/* @var $room common\models\Rooms */

$this->title = 'Agendar Espaço';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="reservation-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($room): ?>
        <p class="lead mb-4">Agendando: <strong><?= Html::encode($room->nome_sala) ?></strong></p>
    <?php else: ?>
        <div class="alert alert-danger">Erro: Nenhuma sala selecionada.</div>
    <?php endif; ?>

    <div class="reservation-form card border-0 shadow-sm p-4">

        <?php $form = ActiveForm::begin(['options' => ['class' => 'needs-validation']]); ?>

        <!-- ROOM ID (oculto) -->
        <?= $form->field($model, 'room_id')->hiddenInput()->label(false) ?>

        <!-- DATA – BLOQUEADA PARA DATAS PASSADAS -->
        <?= $form->field($model, 'data_reserva')->widget(DatePicker::class, [
            'dateFormat' => 'yyyy-MM-dd',
            'options' => [
                'class' => 'form-control',
                'placeholder' => 'Selecione a data',
                'readonly' => true, // impede digitar
            ],
            'clientOptions' => [
                'changeMonth' => true,
                'changeYear'  => true,
                'minDate'     => new \yii\web\JsExpression('new Date()'), // ← HOJE (bloqueia passado)
                'maxDate'     => '+12M', // opcional: até 1 ano
                'showButtonPanel' => true,
            ],
        ]) ?>

        <!-- HORA INÍCIO -->
        <?= $form->field($model, 'hora_inicio_agendada')->widget(MaskedInput::class, [
            'mask' => '99:99',
            'options' => [
                'class' => 'form-control',
                'placeholder' => 'Ex: 17:00',
            ],
        ]) ?>
        <!-- HORA FIM -->
        <?= $form->field($model, 'hora_fim_agendada')->widget(MaskedInput::class, [
            'mask' => '99:99',
            'options' => [
                'class' => 'form-control',
                'placeholder' => 'Ex: 18:00',
            ],
        ]) ?>

        <!-- STATUS OCULTO -->
        <?= $form->field($model, 'status')->hiddenInput(['value' => 'pendente'])->label(false) ?>

        <div class="form-group mt-4">
            <?= Html::submitButton('Confirmar Reserva', ['class' => 'btn btn-success btn-lg px-5']) ?>
            <?= Html::a('Cancelar', ['/dashboard/index'], ['class' => 'btn btn-outline-secondary btn-lg px-5 ms-3']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>