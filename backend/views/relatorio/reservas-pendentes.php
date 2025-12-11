<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Reservas Pendentes - Cancelar/Excluir';
?>

<div class="reservas-pendentes">
    <h1><i class="fa fa-exclamation-triangle text-warning"></i> <?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <div class="panel panel-warning">
        <div class="panel-heading">
            <strong>Filtro por Sala</strong>
            <?= Html::dropDownList('sala', $salaId, $salasList, [
                'class' => 'form-control',
                'prompt' => 'Todas as salas',
                'onchange' => 'window.location = "' . Url::to(['reservas-pendentes']) . '?sala=" + this.value'
            ]) ?>
        </div>
    </div>

    <?= Html::beginForm(['reservas-pendentes'], 'post') ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => 'Mostrando <b>{totalCount}</b> reservas pendentes',
        'tableOptions' => ['class' => 'table table-bordered table-striped'],
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn'],

            [
                'attribute' => 'room.nome_sala',
                'label' => 'Sala/Mesa',
            ],
            [
                'attribute' => 'customer.nome',
                'label' => 'Cliente',
            ],
            [
                'label' => 'Data/Hora',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDatetime($model->hora_inicio_agendada, 'dd/MM/yyyy HH:mm') .
                        ' → ' .
                        Yii::$app->formatter->asTime($model->hora_fim_agendada, 'HH:mm');
                }
            ],
            'tipo_reserva',
            [
                'label' => 'Valor',
                'value' => fn($m) => Yii::$app->formatter->asCurrency($m->valor_total ?? 0, 'EUR'),
                'contentOptions' => ['class' => 'text-right'],
            ],
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Cancelar Selecionadas', [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Tem certeza que quer cancelar as reservas selecionadas?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Voltar', ['site/index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?= Html::endForm() ?>
</div>