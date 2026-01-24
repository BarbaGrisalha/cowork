<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Reservas Futuras (Mês Seguinte em Diante)';
?>

<div class="clientes-futuros-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p class="lead">Reservas a partir do próximo mês.</p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => 'Total de <b>{totalCount}</b> reservas futuras',
        'emptyText' => '<div class="alert alert-info text-center"><h4>Nenhuma reserva futura agendada.</h4></div>',
        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover align-middle'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'cliente_nome',
                'label' => 'Cliente',
                'format' => 'raw',
                'value' => fn($m) => '<strong>' . Html::encode($m['cliente_nome']) . '</strong>',
            ],

            [
                'attribute' => 'reservation_code',
                'label' => 'Código Reserva',
                'format' => 'raw',
                'value' => fn($m) => '<code class="bg-success text-white px-2 py-1 rounded">' . Html::encode($m['reservation_code'] ?? '—') . '</code>',
            ],

            [
                'attribute' => 'nome_sala',
                'label' => 'Sala',
                'format' => 'raw',
                'value' => fn($m) => '<span class="badge bg-primary">' . Html::encode($m['nome_sala']) . '</span>',
            ],

            [
                'label' => 'Horário',
                'format' => 'raw',
                'value' => fn($m) => Yii::$app->formatter->asDatetime($m['inicio'], 'dd/MM/yyyy HH:mm') . ' → ' .
                    Yii::$app->formatter->asTime($m['fim'], 'HH:mm'),
            ],
        ],
    ]); ?>

</div>