<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap5\Modal;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Histórico Completo de Reservas';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['/site/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="reservation-historic">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-arrow-left"></i> Voltar ao Dashboard', ['/site/index'], ['class' => 'btn btn-outline-primary']) ?>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <?php Pjax::begin(['id' => 'pjax-reservas']) ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => 'Mostrando {begin}–{end} de {totalCount} reservas',
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    // CÓDIGO
                    [
                        'attribute' => 'reservation_code',
                        'format' => 'raw',
                        'value' => fn($m) => '<span class="badge bg-primary fs-6 px-3 py-2">' . Html::encode($m->reservation_code) . '</span>',
                    ],

                    // ESPAÇO
                    'room.nome_sala',

                    // DATA E HORÁRIO
                    [
                        'label' => 'Data / Horário',
                        'format' => 'raw',
                        'value' => fn($m) => Yii::$app->formatter->asDate($m->hora_inicio_agendada, 'dd/MM/yyyy') . '<br>' .
                                           '<small class="text-muted">' .
                                           Yii::$app->formatter->asTime($m->hora_inicio_agendada, 'HH:mm') . ' - ' .
                                           Yii::$app->formatter->asTime($m->hora_fim_agendada, 'HH:mm') .
                                           '</small>',
                    ],

                    // VALOR
                    [
                        'attribute' => 'total_estimado',
                        'value' => fn($m) => '€ ' . number_format($m->total_estimado, 2),
                    ],

                    // STATUS
                    [
                        'label' => 'Status',
                        'format' => 'raw',
                        'value' => fn($m) => $m->hasPaidPayment()
                            ? '<span class="badge bg-success">Pago</span>'
                            : ($m->status === 'cancelada'
                                ? '<span class="badge bg-danger">Cancelada</span>'
                                : '<span class="badge bg-warning text-dark">Pendente</span>'),
                    ],

                    // BOTÃO DETALHES → abre o modal
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a(
                                    '<i class="fas fa-eye"></i>',
                                    '#',
                                    [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'data-bs-toggle' => 'modal',
                                        'data-bs-target' => '#modalReserva-' . $model->id,
                                        'title' => 'Ver comprovante',
                                    ]
                                );
                            },
                        ],
                    ],
                ],
            ]); ?>

            <?php Pjax::end() ?>
        </div>
    </div>
</div>

<!-- ====================== MODAL COMPROVANTE (igual ao do pagamento) ====================== -->
<?php foreach ($dataProvider->getModels() as $reserva): ?>
    <?php Modal::begin([
        'id' => 'modalReserva-' . $reserva->id,
        'title' => '<h3 class="modal-title text-center w-100"><i class="fas fa-ticket-alt me-2"></i> Comprovante da Reserva</h3>',
        'size' => Modal::SIZE_LARGE,
        'centerVertical' => true,
        'headerOptions' => ['class' => 'border-0 pb-0'],
        'bodyOptions' => ['class' => 'pt-0'],
    ]); ?>

    <div class="text-center p-4">
        <div class="bg-white rounded-4 shadow-sm p-5 border" style="max-width: 520px; margin: 0 auto;">
            <h2 class="text-primary fw-bold">Cowork IPLeiria</h2>
            <small class="text-muted d-block mb-4">Espaço de Trabalho Colaborativo</small>

            <div class="border-top border-bottom py-4 mb-4">
                <p class="mb-2"><strong>Código da Reserva</strong></p>
                <h1 class="display-5 fw-bold text-success"><?= Html::encode($reserva->reservation_code) ?></h1>
            </div>

            <div class="row text-start mb-4 g-3">
                <div class="col-6"><strong>Cliente:</strong></div>
                <div class="col-6"><?= Html::encode(Yii::$app->user->identity->username) ?></div>

                <div class="col-6"><strong>Espaço:</strong></div>
                <div class="col-6 text-primary fw-bold"><?= Html::encode($reserva->room->nome_sala) ?></div>

                <div class="col-6"><strong>Data:</strong></div>
                <div class="col-6"><?= Yii::$app->formatter->asDate($reserva->hora_inicio_agendada, 'dd/MM/yyyy') ?></div>

                <div class="col-6"><strong>Horário:</strong></div>
                <div class="col-6">
                    <?= Yii::$app->formatter->asTime($reserva->hora_inicio_agendada, 'HH:mm') ?> - 
                    <?= Yii::$app->formatter->asTime($reserva->hora_fim_agendada, 'HH:mm') ?>
                </div>

                <div class="col-6"><strong>Valor:</strong></div>
                <div class="col-6 fw-bold text-success">
                    € <?= number_format($reserva->total_estimado, 2) ?>
                </div>

                <div class="col-6"><strong>Status:</strong></div>
                <div class="col-6">
                    <?php if ($reserva->hasPaidPayment()): ?>
                        <span class="badge bg-success fs-6">Pago</span>
                    <?php elseif ($reserva->status === 'cancelada'): ?>
                        <span class="badge bg-danger fs-6">Cancelada</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark fs-6">Pendente</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir Comprovante
                </button>
            </div>

            <!-- QR CODE OPCIONAL (descomenta se quiser) -->
            <!--
            <div class="mt-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($reserva->reservation_code) ?>" 
                     alt="QR Code" class="img-fluid rounded shadow">
                <p class="small text-muted mt-2">Escaneie para check-in rápido</p>
            </div>
            -->
        </div>
    </div>

    <?php Modal::end(); ?>
<?php endforeach; ?>

<?php
$this->registerCss("
    .badge { font-size: 0.9em; }
    .grid-view th { background-color: #f8f9fa; }
");
?>