<?php

use yii\helpers\Html; // OBRIGATÓRIO
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $reservation common\models\Reservations */
/* @var $model frontend\models\FakeCardForm */ // 🚨 Aponta para o seu Model de cartão completo

$this->title = 'Checkout de Pagamento FAKE (Completo)';
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
            'enableAjaxValidation' => false,
        ]); ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'card_number')->textInput(['maxlength' => true, 'placeholder' => 'Ex: 4xxx xxxx xxxx xxxx (Visa FAKE)']) ?>
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
                <?= $form->field($model, 'cvv')->passwordInput(['maxlength' => 4, 'placeholder' => 'CVV']) ?>
            </div>
        </div>

        <p class="text-muted small">
            **Regras de Simulação FAKE:**
        <ul>
            <li>Para **SUCESSO** (APROVADO), use '4111111111111111', Visa Fake.</li>
            <li>Mês 10 </li>
            <li>Ano 2030 </li>
            <li>CVV 123 </li>
        </ul>

        </p>

        <div class="form-group">
            <?= Html::submitButton('Simular Pagamento', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>