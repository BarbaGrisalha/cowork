<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Salas/Mesas';
?>

<div class="rooms-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'nome_sala',
            'capacidade',
            [
                'attribute' => 'status',
                'value' => fn($m) => $m->status === 'ativa' ? 'Ativa' : 'Inativa',
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update}',
            ],
        ],
    ]); ?>

</div>