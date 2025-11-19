<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $reservation common\models\Reservations */

$this->title = 'Pagamento Aprovado!';
?>

<div class="pagamento-sucesso">
    <h1>🎉 <?= Html::encode($this->title) ?></h1>
    <hr>
    <div class="alert alert-success">
        <p>Parabéns, Altamir! O pagamento FAKE da sua reserva #<?= $reservation->id ?> foi **APROVADO** pelo nosso Gateway FAKE com sucesso.</p>
        <p>A sala **<?= Html::encode($reservation->room->name) ?>** está reservada para você, do dia <?= Yii::$app->formatter->asDate($reservation->start_time) ?> à <?= Yii::$app->formatter->asTime($reservation->end_time) ?>.</p>
        <p>O preço total da reserva foi de: <strong>R$ <?= number_format($reservation->total_price, 2, ',', '.') ?></strong>.</p>
    </div>

    <p>
        <?= Html::a('Voltar para o Dashboard', ['/site/index'], ['class' => 'btn btn-primary']) ?>
    </p>
</div>