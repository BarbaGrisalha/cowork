<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $barItems array */
/* @var $equipment array */
/* @var $locations array */
/* @var $userReservations array */

$this->title = 'Painel do Cliente | Cowork';
?>

<div class="dashboard-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="lead">Bem-vindo(a), <?= strtoupper(Yii::$app->user->identity->username) ?>! Aqui está o resumo do seu Cowork.</p>

    <div class="row mt-5">
        <!-- Minhas Próximas Reservas -->
        <h3 class="mt-5">Minhas Próximas Reservas</h3>

        <?php if (empty($userReservations)): ?>
            <div class="alert alert-info text-center py-4">
                <i class="bi bi-calendar-x fs-4 me-2"></i>
                Você ainda não tem reservas agendadas.
                <div class="mt-3">
                    <?= Html::a('Fazer Nova Reserva', ['reservation/escolher'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userReservations as $reserva): ?>
                            <?php
                            $inicio = Yii::$app->formatter->asDatetime($reserva->hora_inicio_agendada, 'php:d/m/Y H:i');
                            $fim    = Yii::$app->formatter->asTime($reserva->hora_fim_agendada, 'php:H:i');
                            $data   = Yii::$app->formatter->asDate($reserva->hora_inicio_agendada, 'php:d/m/Y');
                            $sala   = $reserva->room ? Html::encode($reserva->room->nome_sala) : '—';
                            $statusClass = match ($reserva->status) {
                                'pendente'   => 'warning',
                                'Confirmado' => 'success',
                                'cancelada'  => 'danger',
                                default      => 'secondary',
                            };
                            ?>
                            <tr>
                                <td><?= Html::encode($reserva->id) ?></td>
                                <td>
                                    <strong><?= Html::encode($reserva->reservation_code ?? '—') ?></strong>
                                    <br><small class="text-muted"><?= $sala ?></small>
                                </td>
                                <td><?= $data ?></td>
                                <td><?= $inicio ?> – <?= $fim ?></td>
                                <td>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= ucfirst($reserva->status) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('<i class="bi bi-eye"></i> Ver', ['reservation/view', 'id' => $reserva->id], [
                                        'class' => 'btn btn-sm btn-outline-primary me-1'
                                    ]) ?>
                                    <?php if ($reserva->status !== 'cancelada'): ?>
                                        <?= Html::a('<i class="bi bi-trash"></i> Cancelar', ['reservation/cancel', 'id' => $reserva->id], [
                                            'class' => 'btn btn-sm btn-outline-danger',
                                            'data' => [
                                                'confirm' => 'Tem certeza que deseja cancelar esta reserva?',
                                                'method' => 'post',
                                            ]
                                        ]) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>


        <?php endif; ?>

        <!-- ==================== LATERAL DIREITA ==================== -->
        <div class="col-lg-4">
            <h2>Inventário & Consumo</h2>

            <h5 class="mt-4">Bar / Economato</h5>
            <?php if (empty($barItems)): ?>
                <p class="text-muted small">Sem itens no momento.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($barItems as $item): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= Html::encode($item->nome_item ?? 'Item') ?></span>
                            <strong>R$ <?= number_format($item->preco_venda ?? 0, 2) ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h5 class="mt-4">Equipamentos Extras</h5>
            <?php if (empty($equipment)): ?>
                <p class="text-muted small">Nada disponível.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($equipment as $eq): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= Html::encode($eq->nome_item ?? 'Equipamento') ?></span>
                            <strong>R$ <?= number_format($eq->preco_extra ?? 0, 2) ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerJsFile('https://unpkg.com/mqtt/dist/mqtt.min.js', ['position' => \yii\web\View::POS_END]);
$this->registerJs(
    <<<JS
const client = mqtt.connect('ws://localhost:8080/mqtt'); // se usar websockets, ou muda pra mosquitto com websocket

client.on('connect', function () {
    client.subscribe('cowork/reservas/nova');
    console.log('MQTT conectado e inscrito!');
});

client.on('message', function (topic, message) {
    const reserva = JSON.parse(message.toString());

    // TOAST NOTIFICAÇÃO (Bootstrap toast)
    const toastHtml = `
<div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
    <div class="d-flex">
        <div class="toast-body">
            <strong>Nova reserva!</strong><br>
            \${reserva.room} - \${reserva.date} \${reserva.time}<br>
            Cliente: \${reserva.customer} (Código: \${reserva.code})
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>`; // <--- As barras invertidas foram adicionadas aqui

    document.body.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.querySelector('.toast:last-child');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
});
JS
);
?>

<!-- Bootstrap Icons (se ainda não tiver) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">