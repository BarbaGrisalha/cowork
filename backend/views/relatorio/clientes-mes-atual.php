<?php

use yii\grid\GridView;
use yii\helpers\Html;

$mesBonito = Yii::$app->formatter->asDate($mes . '-01', 'MMMM yyyy');
$this->title = "Clientes – Faturamento de $mesBonito";
?>

<div class="clientes-mes-atual-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p class="lead">
        Período: <strong><?= $mesBonito ?></strong>
        <?= Html::a('← Mês anterior', ['clientes-mes-atual', 'mes' => date('Y-m', strtotime($mes . '-01 -1 month'))], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?= Html::a('Próximo mês →', ['clientes-mes-atual', 'mes' => date('Y-m', strtotime($mes . '-01 +1 month'))], ['class' => 'btn btn-sm btn-outline-secondary ms-2']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => 'Total de <b>{totalCount}</b> clientes com movimento neste mês',
        'emptyText' => '<div class="alert alert-warning text-center"><h4>Nenhum cliente com reserva ou pagamento neste mês.</h4></div>',
        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover align-middle'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'nome',
                'label' => 'Cliente',
                'format' => 'raw',
                'value' => fn($m) => '<strong>' . Html::encode($m['nome']) . '</strong>',
            ],

            [
                'attribute' => 'num_reservas',
                'label' => 'Nº Reservas',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],

            [
                'attribute' => 'salas_ocupadas',
                'label' => 'Salas Ocupadas',
                'format' => 'raw',
                'value' => function ($m) {
                    if (!$m['salas_ocupadas']) {
                        return '<span class="text-muted">—</span>';
                    }
                    $salas = explode(', ', $m['salas_ocupadas']);
                    $html = '';
                    foreach ($salas as $sala) {
                        $html .= '<span class="badge bg-info me-1">' . Html::encode(trim($sala)) . '</span>';
                    }
                    return $html;
                },
            ],


            [
                'attribute' => 'total_pago',
                'label' => 'Pago (€)',
                'format' => ['currency', 'EUR'],
                'contentOptions' => ['class' => 'text-end fw-bold text-success'],
            ],

        ],
    ]); ?>

</div>