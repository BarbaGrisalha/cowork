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
        <?= Html::a('← Mês anterior', ['clientes-mes-atual', 'mes' => date('Y-m', strtotime($mes . '-01 -1 month'))], ['class' => 'btn btn-sm btn-default']) ?>
        <?= Html::a('Próximo mês →', ['clientes-mes-atual', 'mes' => date('Y-m', strtotime($mes . '-01 +1 month'))], ['class' => 'btn btn-sm btn-default']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => 'Total de <b>{totalCount}</b> clientes com movimento neste mês',
        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'nome',
                'label' => 'Cliente',
                'format' => 'raw',
                'value' => fn($m) => '<strong>' . Html::encode($m['nome']) . '</strong>',
            ],

            [
                'attribute' => 'salas_ocupadas',
                'label' => 'Salas/Mesas Ocupadas',
                'format' => 'raw',
                'value' => function ($m) {
                    if (!$m['salas_ocupadas']) {
                        return '<span class="text-muted">—</span>';
                    }
                    $salas = explode(', ', $m['salas_ocupadas']);
                    $html = '';
                    foreach ($salas as $sala) {
                        $html .= '<span class="label label-info" style="margin:1px;">' . Html::encode($sala) . '</span> ';
                    }
                    return $html;
                },
            ],

            [
                'label' => 'Total Pago (€)',
                'format' => 'raw',
                'contentOptions' => ['class' => 'text-right text-success font-weight-bold'],
                'value' => fn($m) => Yii::$app->formatter->asCurrency($m['total_pago'], 'EUR'),
            ],
        ],
    ]); ?>

    <?php if (empty($dataProvider->getModels())): ?>
        <div class="alert alert-warning text-center">
            <h4>Nenhum cliente com pagamento ou reserva neste mês.</h4>
        </div>
    <?php endif; ?>

</div>