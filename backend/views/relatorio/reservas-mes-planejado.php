<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Reservas do Mês (Planejado) - ' . $mesBonito;
?>

<div class="reservas-mes-planejado">

    <h1><?= Html::encode($this->title) ?></h1>

    <p class="lead">
        Todas as reservas confirmadas do mês, incluindo futuras
        <?= Html::a('← Mês anterior', ['reservas-mes-planejado', 'mes' => date('Y-m', strtotime($mes . '-01 -1 month'))], ['class' => 'btn btn-sm btn-default']) ?>
        <?= Html::a('Próximo mês →', ['reservas-mes-planejado', 'mes' => date('Y-m', strtotime($mes . '-01 +1 month'))], ['class' => 'btn btn-sm btn-default']) ?>
    </p>

    <!-- TOTAIS GERAIS -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Previsto</span>
                    <span class="info-box-number"><?= Yii::$app->formatter->asCurrency($totalGeralPrevisto, 'EUR') ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Já Pago</span>
                    <span class="info-box-number"><?= Yii::$app->formatter->asCurrency($totalGeralPago, 'EUR') ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendente</span>
                    <span class="info-box-number"><?= Yii::$app->formatter->asCurrency($totalGeralPendente, 'EUR') ?></span>
                </div>
            </div>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'cliente_nome:ntext:Cliente',
            'salas:ntext:Salas',
            'total_reservas:text:Reservas',
            [
                'attribute' => 'valor_previsto',
                'format' => ['decimal', 2],
                'label' => 'Previsto (€)',
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'valor_pago',
                'format' => ['decimal', 2],
                'label' => 'Pago (€)',
                'contentOptions' => ['class' => 'text-right text-success'],
            ],
            [
                'attribute' => 'valor_pendente',
                'format' => ['decimal', 2],
                'label' => 'Pendente (€)',
                'contentOptions' => ['class' => 'text-right text-warning font-weight-bold'],
            ],
        ],
    ]); ?>

</div>