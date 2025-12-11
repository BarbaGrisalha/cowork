<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$mesBonito = Yii::$app->formatter->asDate($mes . '-01', 'MMMM yyyy');
$this->title = "Reservas por Sala/Mesa – $mesBonito";
?>

<div class="reservas-salas-index">

    <!-- FILTROS -->
    <div class="panel panel-primary">
        <div class="panel-heading"><strong>Filtros</strong></div>
        <div class="panel-body">
            <form method="get" class="form-inline">
                <input type="hidden" name="mes" value="<?= $mes ?>">

                <?= Html::dropDownList('sala', $filters['sala'], $salasList, [
                    'class' => 'form-control input-sm',
                    'prompt' => 'Todas as salas'
                ]) ?>

                <?= Html::dropDownList('tipo', $filters['tipo'], [
                    'hora' => 'Hora',
                    'diaria' => 'Diária',
                    'mensal' => 'Mensal'
                ], ['class' => 'form-control input-sm', 'prompt' => 'Todos os tipos']) ?>

                <?= Html::dropDownList('status_reserva', $filters['status_reserva'], [
                    'pendente' => 'Pendente',
                    'confirmada' => 'Confirmada',
                    'concluida' => 'Concluída'
                ], ['class' => 'form-control input-sm', 'prompt' => 'Todos os status']) ?>

                <?= Html::dropDownList('faturado', $filters['faturado'], [
                    '' => 'Todos',
                    'pago' => 'Pago',
                    'pendente' => 'Pendente'
                ], ['class' => 'form-control input-sm']) ?>

                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <?= Html::a('Limpar', ['reservas-salas', 'mes' => $mes], ['class' => 'btn btn-default btn-sm']) ?>
            </form>
        </div>
    </div>

    <!-- NAVEGAÇÃO DE MÊS -->
    <p class="text-center">
        <?= Html::a('← Mês anterior', ['reservas-salas', 'mes' => date('Y-m', strtotime($mes . '-01 -1 month'))], ['class' => 'btn btn-default']) ?>
        <strong><?= $mesBonito ?></strong>
        <?= Html::a('Próximo mês →', ['reservas-salas', 'mes' => date('Y-m', strtotime($mes . '-01 +1 month'))], ['class' => 'btn btn-default']) ?>
    </p>

    <!-- DADOS COM TOTAL POR SALA -->
    <?php if (empty($porSala)): ?>
        <div class="alert alert-info text-center">
            <h4>Nenhuma reserva encontrada com os filtros atuais.</h4>
        </div>
    <?php else: ?>
        <?php foreach ($porSala as $sala => $reservas):
            $totalSala = $totaisPorSala[$sala] ?? 0;
        ?>
            <div class="panel panel-default mb-4">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <strong><?= Html::encode($sala) ?></strong>
                        <span class="badge"><?= count($reservas) ?> reserva(s)</span>
                        <span class="pull-right text-success">
                            <strong>Total no Mês: <?= Yii::$app->formatter->asCurrency($totalSala, 'EUR') ?></strong>
                        </span>
                    </h3>
                </div>
                <div class="panel-body p-0">
                    <table class="table table-condensed table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Data/Hora</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Situação</th>
                                <th class="text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $res): ?>
                                <tr>
                                    <td>
                                        <?= Yii::$app->formatter->asDate($res['hora_inicio_agendada'], 'dd/MM') ?>
                                        <small class="text-muted">
                                            <?= Yii::$app->formatter->asTime($res['hora_inicio_agendada'], 'HH:mm') ?> →
                                            <?= Yii::$app->formatter->asTime($res['hora_fim_agendada'], 'HH:mm') ?>
                                        </small>
                                    </td>
                                    <td><?= Html::encode($res['cliente_nome'] ?? '—') ?></td>
                                    <td>
                                        <span class="label label-<?= $res['tipo_reserva'] == 'hora' ? 'info' : ($res['tipo_reserva'] == 'diaria' ? 'success' : 'warning') ?>">
                                            <?= ucfirst($res['tipo_reserva']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($res['situacao_pagamento'] == 'pago'): ?>
                                            <span class="label label-success">Pago</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Pendente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right text-<?= $res['valor_pago'] > 0 ? 'success' : 'muted' ?>">
                                        <strong><?= Yii::$app->formatter->asCurrency($res['valor_pago'], 'EUR') ?></strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>