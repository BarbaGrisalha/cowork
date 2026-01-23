<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Reservation */

$this->title = 'Reserva #' . $model->id . ' - Detalhes';
$this->params['breadcrumbs'][] = ['label' => 'Minhas Reservas', 'url' => ['my-reservations']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="reservation-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card shadow-sm p-4">
        <div class="row">
            <div class="col-md-6">
                <h5 class="fw-bold">Código da Reserva</h5>
                <p class="text-muted"><?= Html::encode($model->reservation_code ?? '—') ?></p>

                <h5 class="fw-bold mt-4">Sala</h5>
                <p class="text-muted"><?= Html::encode($model->room->nome_sala ?? '—') ?></p>

                <h5 class="fw-bold mt-4">Data</h5>
                <p class="text-muted"><?= Yii::$app->formatter->asDate($model->hora_inicio_agendada, 'dd/MM/yyyy') ?></p>
            </div>

            <div class="col-md-6">
                <h5 class="fw-bold">Horário</h5>
                <p class="text-muted">
                    De: <?= Yii::$app->formatter->asTime($model->hora_inicio_agendada, 'HH:mm') ?><br>
                    Até: <?= Yii::$app->formatter->asTime($model->hora_fim_agendada, 'HH:mm') ?>
                </p>

                <h5 class="fw-bold mt-4">Status</h5>
                <span class="badge bg-<?= $model->status === 'pendente' ? 'warning' : ($model->status === 'Confirmado' ? 'success' : 'danger') ?>">
                    <?= ucfirst($model->status) ?>
                </span>

                <h5 class="fw-bold mt-4">Valor Total</h5>
                <p class="text-muted">€ <?= number_format($model->total_estimado, 2) ?></p>
            </div>
        </div>

        <div class="mt-4 text-end">
            <?php if ($model->status !== 'cancelada'): ?>
                <?= Html::a('Cancelar Reserva', ['cancel', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger',
                    'data' => ['confirm' => 'Tem certeza?', 'method' => 'post']
                ]) ?>
            <?php endif; ?>
            <?= Html::a('Voltar', ['my-reservations'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
        </div>
    </div>
</div>