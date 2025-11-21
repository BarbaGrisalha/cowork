<?php
// /frontend/views/payment/falha.php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $reservation frontend\models\Reservations */

$this->title = 'Falha no Pagamento';
?>

<div class="pagamento-falha">
    <h1><?= Html::encode($this->title) ?></h1>
    <hr>

    <div class="alert alert-danger">
        <h2>❌ Pagamento Não Aprovado!</h2>
        <p>A simulação de pagamento da Reserva #<?= $reservation->id ?> falhou. Status atual: 
           <strong><?= $reservation->status ?></strong>.
        </p>
        
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <p><strong>Detalhes do Erro:</strong> <?= Yii::$app->session->getFlash('error') ?></p>
        <?php endif; ?>
        
        <p>Por favor, tente novamente ou entre em contato com o suporte.</p>
    </div>

    <?= Html::a('Tentar Novo Pagamento', ['checkout', 'reservation_id' => $reservation->id], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Voltar para Minhas Reservas', ['/reservas'], ['class' => 'btn btn-default']) ?>
</div>