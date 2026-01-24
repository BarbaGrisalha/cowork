<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Utilizadores';
?>

<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            'email',
            'status' => [
                'attribute' => 'status',
                'value' => fn($m) => $m->status == 10 ? 'Ativo' : 'Inativo',
            ],
            'created_at:datetime',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
            ],
        ],
    ]); ?>

</div>