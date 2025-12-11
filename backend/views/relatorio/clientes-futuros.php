<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Clientes com Reservas Futuras';
?>

<div class="relatorio-clientes-futuros">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- FORMULÁRIO DE FILTROS (versão 100% funcional) -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                Filtros
                <small class="pull-right">
                    <?= Html::a('Limpar tudo', ['clientes-futuros'], ['class' => 'text-danger']) ?>
                </small>
            </h4>
        </div>
        <div class="panel-body">
            <form method="get" class="form-inline">
                <!-- todos os inputs acima aqui -->
                <!-- (copie o bloco grande que mandei acima) -->
            </form>
        </div>
    </div>

    <!-- GRID -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'layout' => "{summary}\n<div class='table-responsive'>{items}</div>\n{pager}",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'nome',
                'label' => 'Cliente',
                'value' => fn($m) => '<strong>' . Html::encode($m['nome']) . '</strong>',
                'format' => 'raw'
            ],

            [
                'attribute' => 'nome_sala',
                'label' => 'Sala/Mesa',
                'value' => fn($m) => '<span class="label label-primary">' . Html::encode($m['nome_sala']) . '</span>',
                'format' => 'raw'
            ],

            [
                'label' => 'Período',
                'format' => 'raw',
                'value' => fn($m) => Yii::$app->formatter->asDatetime($m['inicio'], 'dd/MM/yyyy HH:mm') . ' → ' .
                    Yii::$app->formatter->asDatetime($m['fim'], 'HH:mm')
            ],

            [
                'attribute' => 'tipo_reserva',
                'label' => 'Tipo',
                'format' => 'raw',
                'value' => fn($m) => '<span class="label label-' .
                    ($m['tipo_reserva'] == 'hora' ? 'info' : ($m['tipo_reserva'] == 'diaria' ? 'success' : 'warning')) .
                    '">' . ucfirst($m['tipo_reserva']) . '</span>'
            ],

            [
                'attribute' => 'status',
                'label' => 'Status',
                'format' => 'raw',
                'value' => function ($m) {
                    $cores = ['pendente' => 'warning', 'confirmada' => 'success', 'concluida' => 'primary'];
                    $cor = $cores[$m['status']] ?? 'default';
                    $texto = ['pendente' => 'Pendente', 'confirmada' => 'Confirmada', 'concluida' => 'Concluída'][$m['status']] ?? $m['status'];
                    return '<span class="label label-' . $cor . '">' . $texto . '</span>';
                }
            ],
        ],
    ]); ?>
</div>