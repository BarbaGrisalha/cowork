<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $reservation common\models\Reservations */
/* @var $model frontend\models\CartaoFakeForm */

$this->title = 'Checkout de Pagamento FAKE';
?>

<div class="pagamento-checkout">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Reserva #<?= $reservation->id ?> | Valor: R$ <?= number_format($reservation->total_estimado, 2, ',', '.') ?></p>
    <hr>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="card-form-mock">
        <?php $form = ActiveForm::begin([
            'id' => 'fake-payment-form',
            'enableAjaxValidation' => false, // Não complique a vida, valide no servidor.
        ]); ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'card_number')->textInput(['maxlength' => true, 'placeholder' => 'Ex: 4xxx xxxx xxxx xxxx (Visa)']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'card_name')->textInput(['maxlength' => true, 'placeholder' => 'Nome como no cartão']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'expiry_month')->textInput(['maxlength' => 2, 'placeholder' => 'Mês (MM)']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'expiry_year')->textInput(['maxlength' => 4, 'placeholder' => 'Ano (AAAA)']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'cvc')->passwordInput(['maxlength' => 4, 'placeholder' => 'CVC']) ?>
            </div>
        </div>

        <p class="text-muted small">Aqui só aceitamos cartões FAKE Visa, Mastercard, Amex e Diners. E sim, tem que passar no Luhn Check.</p>

        <div class="form-group">
            <?= Html::submitButton('Simular Pagamento', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>