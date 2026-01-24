<?php

use yii\grid\GridView;
use yii\helpers\Html;

$mesBonito = Yii::$app->formatter->asDate($mes . '-01', 'MMMM yyyy');
$this->title = "Ranking de Salas/Mesas – $mesBonito";
?>

<div class="salas-ranking-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p class="lead">
        Período: <strong><?= $mesBonito ?></strong>
        <?= Html::a('← Mês anterior', ['salas-ranking', 'mes' => date('Y-m', strtotime($mes . '-01 -1 month'))], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?= Html::a('Próximo mês →', ['salas-ranking', 'mes' => date('Y-m', strtotime($mes . '-01 +1 month'))], ['class' => 'btn btn-sm btn-outline-secondary ms-2']) ?>
    </p>

    <?php
    $totalReservas = 0;
    $totalFaturado = 0;
    foreach ($dataProvider->getModels() as $model) {
        $totalReservas += $model['total_reservas'];
        $totalFaturado += $model['valor_total'];
    }
    ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <strong>Total de Reservas: <?= $totalReservas ?></strong>
            </div>
            <div>
                <strong class="text-success">Total Faturado: <?= Yii::$app->formatter->asCurrency($totalFaturado, 'EUR') ?></strong>
            </div>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'emptyText' => '<div class="alert alert-info text-center py-5"><h4>Nenhum movimento registrado em ' . $mesBonito . '.</h4></div>',
        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover align-middle'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'nome_sala',
                'label' => 'Sala/Mesa',
                'format' => 'raw',
                'value' => fn($m) => '<strong>' . Html::encode($m['nome_sala']) . '</strong>',
            ],

            [
                'attribute' => 'total_reservas',
                'label' => 'Nº Reservas',
                'contentOptions' => ['class' => 'text-center fw-bold'],
            ],

            [
                'attribute' => 'valor_total',
                'label' => 'Faturado (€)',
                'format' => ['currency', 'EUR'],
                'contentOptions' => ['class' => 'text-end fw-bold text-success'],
            ],
        ],
    ]); ?>

</div>