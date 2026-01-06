<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\Reservation;

/* @var $this yii\web\View */
/* @var $reservations common\models\Reservation[] */
/* @var $currentStatus string */

$this->title = 'Minhas Reservas';
$this->params['breadcrumbs'][] = $this->title;

$statusOptions = [
    'todas' => 'Todas',
    'Confirmado' => 'Confirmadas',
    'Pendente' => 'Pendentes',
    'cancelada' => 'Canceladas',
    'FALHA' => 'Com Falha',
];

?>

<div class="my-reservations py-5">
    <div class="container">
        <h1 class="text-center mb-4 fw-bold text-primary">
            <i class="fas fa-calendar-check me-3"></i> Minhas Reservas
        </h1>

        <!-- TABS DE FILTRO -->
        <ul class="nav nav-tabs justify-content-center mb-5 border-0">
            <?php foreach ($statusOptions as $key => $label): ?>
                <li class="nav-item">
                    <?= Html::a(
                        $label . ' <span class="badge bg-secondary ms-2">' .
                            Reservation::find()
                            ->where(['customer_id' => Yii::$app->user->identity->customer->id ?? 0])
                            ->andWhere($key !== 'todas' ? ['status' => $key] : [])
                            ->count() .
                            '</span>',
                        ['my-reservations', 'status' => $key],
                        [
                            'class' => 'nav-link px-4 py-3 fw-medium ' . ($currentStatus === $key ? 'active text-white bg-primary' : 'text-dark'),
                        ]
                    ) ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- CONTEÚDO -->
        <?php if (empty($reservations)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-5x text-muted mb-4"></i>
                <h3 class="text-muted">Nenhuma reserva encontrada</h3>
                <p class="lead text-muted">
                    <?= $currentStatus === 'todas' ? 'Você ainda não tem reservas.' : 'Nenhuma reserva ' . strtolower($statusOptions[$currentStatus]) . '.' ?>
                </p>
                <?= Html::a('Ver Espaços Disponíveis', ['reservation/escolher'], ['class' => 'btn btn-success btn-lg mt-3']) ?>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($reservations as $res): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 <?= $res->status === 'Confirmado' ? 'border-success border-3' : '' ?>">
                            <div class="card-header bg-transparent border-0 text-center pt-4">
                                <span class="badge bg-<?=
                                                        $res->status === 'Confirmado' ? 'success' : ($res->status === 'Pendente' ? 'warning' : ($res->status === 'cancelada' ? 'secondary' : 'danger'))
                                                        ?> fs-6 px-3 py-2">
                                    <?= Html::encode($res->status) ?>
                                </span>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title fw-bold text-primary">
                                    <?= Html::encode($res->room->nome_sala) ?>
                                </h5>

                                <?php if (!empty($res->reservation_code)): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">Código da Reserva</small>
                                        <h4 class="fw-bold text-success"><?= Html::encode($res->reservation_code) ?></h4>
                                    </div>
                                <?php endif; ?>

                                <p class="mb-2">
                                    <i class="fas fa-calendar-alt me-2 text-info"></i>
                                    <?= Yii::$app->formatter->asDate($res->hora_inicio_agendada, 'dd/MM/yyyy') ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-clock me-2 text-info"></i>
                                    <?= Yii::$app->formatter->asTime($res->hora_inicio_agendada, 'HH:mm') ?>
                                    às
                                    <?= Yii::$app->formatter->asTime($res->hora_fim_agendada, 'HH:mm') ?>
                                </p>
                                <p class="mb-0 fw-bold text-success fs-5">
                                    R$ <?= number_format($res->total_estimado, 2, ',', '.') ?>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-0 text-center pb-4">
                                <?php if ($res->status === 'Confirmado'): ?>
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal-<?= $res->id ?>">
                                        <i class="fas fa-ticket-alt me-1"></i> Ver Comprovante
                                    </button>
                                <?php elseif ($res->status === 'Pendente'): ?>
                                    <span class="text-warning small">Aguardando pagamento</span>
                                <?php elseif ($res->status === 'cancelada'): ?>
                                    <span class="text-muted small">Cancelada</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Modal comprovante -->
                        <?php \yii\bootstrap5\Modal::begin([
                            'id' => 'modal-' . $res->id,
                            'title' => '<h4 class="text-center"><i class="fas fa-ticket-alt me-2"></i> Comprovante</h4>',
                            'size' => \yii\bootstrap5\Modal::SIZE_LARGE,
                        ]); ?>
                        <div class="text-center p-4">
                            <h2>Cowork IPLeiria</h2>
                            <div class="border-top border-bottom py-4 my-4">
                                <p><strong>Código</strong></p>
                                <h1 class="display-5 text-success"><?= Html::encode($res->reservation_code) ?></h1>
                            </div>
                            <p><strong>Espaço:</strong> <?= Html::encode($res->room->nome_sala) ?></p>
                            <p><strong>Data:</strong> <?= Yii::$app->formatter->asDate($res->hora_inicio_agendada, 'dd/MM/yyyy') ?></p>
                            <p><strong>Horário:</strong> <?= Yii::$app->formatter->asTime($res->hora_inicio_agendada, 'HH:mm') ?> - <?= Yii::$app->formatter->asTime($res->hora_fim_agendada, 'HH:mm') ?></p>
                            <p><strong>Valor:</strong> R$ <?= number_format($res->total_estimado, 2, ',', '.') ?></p>
                            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">Imprimir</button>
                        </div>
                        <?php \yii\bootstrap5\Modal::end(); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>