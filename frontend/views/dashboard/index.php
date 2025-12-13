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

        <!-- ==================== SALAS ==================== -->
        <div class="col-lg-8">
            <h2>Escolha sua Sala</h2>
            <p class="text-muted">Selecione o tipo de reserva que deseja fazer:</p>

            <?php if (empty($locations)): ?>
                <div class="alert alert-info">Nenhuma sala disponível no momento.</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($locations as $room): ?>
                        <?php if ($room->status !== 'ativa') continue; ?>

                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden">

                                <!-- FOTO PADRÃO (ou individual no futuro) -->
                                <?php
                                $defaultImage = Yii::getAlias('@frontend/web/uploads/rooms/1.jpeg');
                                if (file_exists($defaultImage)):
                                ?>
                                    <img src="<?= Yii::getAlias('@web') ?>/uploads/rooms/1.jpeg?v=<?= filemtime($defaultImage) ?>"
                                        class="card-img-top"
                                        alt="<?= Html::encode($room->nome_sala) ?>"
                                        style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center text-white" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <div class="text-center">
                                            <i class="bi bi-building fs-1"></i>
                                            <p class="mb-0 mt-2 fw-bold"><?= Html::encode($room->nome_sala) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold"><?= Html::encode($room->nome_sala) ?></h5>
                                    <p class="text-muted small">
                                        <i class="bi bi-people"></i> Até <?= $room->capacidade ?> pessoas
                                    </p>

                                    <div class="mt-auto">
                                        <?= Html::a('<i class="bi bi-clock"></i> Por Hora', [
                                            //'/reservation/create',
                                            'reservation/escolher',
                                            'room_id' => $room->id
                                        ], ['class' => 'btn btn-outline-primary btn-sm w-100 mb-2']) ?>

                                        <?= Html::a('<i class="bi bi-calendar-day"></i> Diária R$ 32', [
                                            //'/reservation/select-daily',
                                            'reservation/escolher',
                                            'room_id' => $room->id
                                        ], ['class' => 'btn btn-success btn-sm w-100 mb-2']) ?>

                                        <?= Html::a('<i class="bi bi-calendar-month"></i> Mensal R$ 225', [
                                            //'/reservation/select-monthly',
                                            'reservation/escolher',
                                            'room_id' => $room->id
                                        ], ['class' => 'btn btn-warning text-white btn-sm w-100']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Minhas Reservas -->
            <h3 class="mt-5">Minhas Próximas Reservas</h3>
            <?php if (empty($userReservations)): ?>
                <p class="text-muted">Você ainda não tem reservas. Escolha uma sala acima!!!</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($userReservations as $res):
                        $roomName = $res->room ? Html::encode($res->room->nome_sala) : 'Sala removida';
                    ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border-start border-success border-4">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold"><?= $roomName ?></h6>
                                    <p class="small text-muted mb-1">
                                        <i class="bi bi-calendar"></i> <?= Yii::$app->formatter->asDate($res->hora_inicio_agendada) ?>
                                        <i class="bi bi-clock ms-3"></i> <?= Yii::$app->formatter->asTime($res->hora_inicio_agendada) ?> - <?= Yii::$app->formatter->asTime($res->hora_fim_agendada) ?>
                                    </p>
                                    <span class="badge bg-<?= $res->status === 'Confirmado' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($res->status) ?>
                                    </span>
                                    <?php if ($res->status !== 'cancelada'): ?>
                                        <?= Html::a('Cancelar', ['/reservation/cancel', 'id' => $res->id], [
                                            'class' => 'btn btn-sm btn-outline-danger float-end',
                                            'data' => ['confirm' => 'Tem certeza?', 'method' => 'post']
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

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