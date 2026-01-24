<?php

use yii\helpers\Html;

$mesBonito = Yii::$app->formatter->asDate($mes . '-01', 'MMMM yyyy');
$this->title = "Reservas por Sala/Mesa – $mesBonito";
?>

<div class="reservas-salas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- FILTROS -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <strong>Filtros</strong>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="mes" value="<?= $mes ?>">

                <div class="col-md-3">
                    <?= Html::dropDownList('sala', $filters['sala'], $salasList, [
                        'class' => 'form-select',
                        'prompt' => 'Todas as salas'
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <?= Html::dropDownList('tipo', $filters['tipo'], [
                        '' => 'Todos os tipos',
                        'hora' => 'Hora',
                        'diaria' => 'Diária',
                        'mensal' => 'Mensal'
                    ], ['class' => 'form-select']) ?>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <?= Html::a('Limpar', ['reservas-salas', 'mes' => $mes], ['class' => 'btn btn-secondary ms-2']) ?>
                </div>
            </form>
        </div>
    </div>

    <!-- NAVEGAÇÃO DE MÊS -->
    <div class="text-center mb-4">
        <?= Html::a('← Mês anterior', ['reservas-salas', 'mes' => date('Y-m', strtotime($mes . '-01 -1 month'))], ['class' => 'btn btn-outline-secondary']) ?>
        <strong class="mx-4 fs-4"><?= $mesBonito ?></strong>
        <?= Html::a('Próximo mês →', ['reservas-salas', 'mes' => date('Y-m', strtotime($mes . '-01 +1 month'))], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php if (empty($porSala)): ?>
        <div class="alert alert-info text-center py-5">
            <h4>Nenhuma reserva encontrada com os filtros atuais.</h4>
        </div>
    <?php else: ?>
        <?php $totalGeral = 0; ?>
        <?php foreach ($porSala as $sala => $reservas):
            $totalSala = $totaisPorSala[$sala] ?? 0;
            $totalGeral += $totalSala;
        ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <strong><?= Html::encode($sala) ?></strong>
                        <span class="badge bg-secondary ms-2"><?= count($reservas) ?> reserva(s)</span>
                    </h5>
                    <span class="text-success fw-bold fs-5">
                        Total: <?= Yii::$app->formatter->asCurrency($totalSala, 'EUR') ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $res): ?>
                                    <tr>
                                        <td>
                                            <?= Yii::$app->formatter->asDate($res['hora_inicio_agendada'], 'dd/MM') ?>
                                            <small class="text-muted d-block">
                                                <?= Yii::$app->formatter->asTime($res['hora_inicio_agendada'], 'HH:mm') ?> –
                                                <?= Yii::$app->formatter->asTime($res['hora_fim_agendada'], 'HH:mm') ?>
                                            </small>
                                        </td>
                                        <td><?= Html::encode($res['cliente_nome'] ?? '—') ?></td>
                                        <td class="text-end fw-bold text-<?= $res['valor_pago'] > 0 ? 'success' : 'muted' ?>">
                                            <?= Yii::$app->formatter->asCurrency($res['valor_pago'], 'EUR') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- TOTAL GERAL DO MÊS -->
        <div class="alert alert-success text-center py-4">
            <h3>Total Geral do Mês: <?= Yii::$app->formatter->asCurrency($totalGeral, 'EUR') ?></h3>
        </div>
    <?php endif; ?>

</div>