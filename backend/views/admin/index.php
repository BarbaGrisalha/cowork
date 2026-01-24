<?php

use yii\helpers\Html;
use yii\helpers\Url;
use dosamigos\chartjs\ChartJs;   // ESSA LINHA!

/* @var $this yii\web\View */
/* @var $clientesFuturos int */
/* @var $faturamentoMes float */
/* @var $totalReservasMes int */
/* @var $pendentes int */
/* @var $mesAtual string */
/* @var $mesesGrafico array */
/* @var $valoresGrafico array */
/* @var $topSalas array */
/* @var $topClientes array */

$this->title = 'BackOffice Cowork';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">

    <div class="row">

        <!-- CLIENTES FUTUROS -->
        <div class="col-lg-6 col-12 mb-4">
            <?= Html::a(
                \hail812\adminlte\widgets\InfoBox::widget([
                    'text' => 'Clientes Futuros',
                    'number' => $clientesFuturos ?? 0,
                    'theme' => 'info',
                    'icon' => 'fas fa-user-clock',
                ]),
                ['relatorio/clientes-futuros'],
                ['class' => 'text-decoration-none']
            ) ?>
        </div>

        <!-- FATURAMENTO DO MÊS -->
        <div class="col-lg-6 col-12 mb-4">
            <?= Html::a(
                \hail812\adminlte\widgets\InfoBox::widget([
                    'text' => 'Faturamento ' . ($mesAtual ?? date('F Y')),
                    'number' => Yii::$app->formatter->asCurrency($faturamentoMes ?? 0, 'EUR'),
                    'theme' => 'success',
                    'icon' => 'fas fa-euro-sign',
                ]),
                ['relatorio/clientes-mes-atual'],
                ['class' => 'text-decoration-none']
            ) ?>
        </div>

    </div>

    <div class="row">

        <!-- RESERVAS POR SALA -->
        <div class="col-lg-6 col-12 mb-4">
            <?= Html::a(
                \hail812\adminlte\widgets\InfoBox::widget([
                    'text' => 'Reservas por Sala',
                    'number' => $totalReservasMes ?? 0,
                    'theme' => 'gradient-primary',
                    'icon' => 'fas fa-building',
                ]),
                ['relatorio/reservas-salas'],
                ['class' => 'text-decoration-none']
            ) ?>
        </div>

        <!-- RESERVAS PENDENTES -->
        <div class="col-lg-6 col-12 mb-4">
            <?= Html::a(
                \hail812\adminlte\widgets\InfoBox::widget([
                    'text' => 'Reservas Pendentes',
                    'number' => $pendentes ?? 0,
                    'theme' => 'warning',
                    'icon' => 'fas fa-exclamation-triangle',
                    'progress' => ($pendentes ?? 0) > 0 ? [
                        'width' => '100%',
                        'description' => 'Precisa de atenção!'
                    ] : null
                ]),
                ['relatorio/reservas-pendentes'],
                ['class' => 'text-decoration-none']
            ) ?>
        </div>


    </div>

    <!-- GRÁFICOS BRUTAIS -->
    <div class="row mt-5">

        <div class="col-xl-8 col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0 font-weight-bold">Faturamento - Últimos 12 Meses</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoFaturamento" height="400"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Top 5 Salas (Este Mês)</h6>
                </div>
                <div class="card-body">
                    <canvas id="graficoTopSalas" height="300"></canvas>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">Top 5 Clientes (Histórico)</h6>
                </div>
                <div class="card-body">
                    <canvas id="graficoTopClientes" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- CHART.JS + GRÁFICOS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico de linha
        new Chart(document.getElementById('graficoFaturamento'), {
            type: 'line',
            data: {
                labels: <?= json_encode($mesesGrafico) ?>,
                datasets: [{
                    label: 'Faturamento (€)',
                    data: <?= json_encode($valoresGrafico) ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Gráfico donut
        new Chart(document.getElementById('graficoTopSalas'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($topSalas, 'nome_sala')) ?: '["Sem dados"]' ?>,
                datasets: [{
                    data: <?= json_encode(array_column($topSalas, 'faturado')) ?: '[0]' ?>,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1']
                }]
            },
            options: {
                responsive: true
            }
        });

        // Gráfico de barras
        new Chart(document.getElementById('graficoTopClientes'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($topClientes, 'nome')) ?: '["Sem dados"]' ?>,
                datasets: [{
                    label: 'Faturado (€)',
                    data: <?= json_encode(array_column($topClientes, 'total')) ?: '[0]' ?>,
                    backgroundColor: '#ffc107'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>