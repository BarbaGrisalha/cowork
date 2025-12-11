<?php

use yii\grid\GridView;
use yii\helpers\Html;

$mesBonito = Yii::$app->formatter->asDate($mes . '-01', 'MMMM yyyy');
$this->title = "Ranking de Salas/Mesas – $mesBonito";
?>

<div class="salas-ranking-index">



    <p class="lead text-muted">
        Período: <strong><?= $mesBonito ?></strong>
        <?= Html::a('← Mês anterior', ['salas-ranking', 'mes' => date('Y-m', strtotime($mes . '-01 -1 month')), 'ordem' => $ordem], ['class' => 'btn btn-xs btn-default']) ?>
        <?= Html::a('Próximo mês →', ['salas-ranking', 'mes' => date('Y-m', strtotime($mes . '-01 +1 month')), 'ordem' => $ordem], ['class' => 'btn btn-xs btn-default']) ?>

        <!-- BOTÃO DE ORDEM -->
        <span class="pull-right">
            <?php if ($ordem === 'desc'): ?>
                <?= Html::a('<i class="fa fa-sort-amount-desc"></i> Maior → Menor', ['salas-ranking', 'mes' => $mes, 'ordem' => 'desc'], ['class' => 'btn btn-primary btn-xs']) ?>
                <?= Html::a('<i class="fa fa-sort-amount-asc"></i> Menor → Maior', ['salas-ranking', 'mes' => $mes, 'ordem' => 'asc'], ['class' => 'btn btn-default btn-xs']) ?>
            <?php else: ?>
                <?= Html::a('<i class="fa fa-sort-amount-desc"></i> Maior → Menor', ['salas-ranking', 'mes' => $mes, 'ordem' => 'desc'], ['class' => 'btn btn-default btn-xs']) ?>
                <?= Html::a('<i class="fa fa-sort-amount-asc"></i> Menor → Maior', ['salas-ranking', 'mes' => $mes, 'ordem' => 'asc'], ['class' => 'btn btn-primary btn-xs']) ?>
            <?php endif; ?>
        </span>
    </p>

    <!-- resto da view igual -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'nome_sala',
                'label' => 'Sala/Mesa',
                'value' => fn($m) => '<strong>' . Html::encode($m['nome_sala']) . '</strong>',
                'format' => 'raw'
            ],

            ['attribute' => 'total_reservas', 'label' => 'Reservas', 'contentOptions' => ['class' => 'text-center']],

            [
                'label' => 'Faturado (€)',
                'format' => 'raw',
                'contentOptions' => ['class' => 'text-right text-success font-weight-bold'],
                'value' => fn($m) => Yii::$app->formatter->asCurrency($m['valor_total'], 'EUR')
            ],
        ],
    ]); ?>

    <?php if (empty($dataProvider->getModels())): ?>
        <div class="alert alert-info text-center">
            <h4>Nenhum movimento registrado em <?= $mesBonito ?> ainda.</h4>
        </div>
    <?php endif; ?>
</div>