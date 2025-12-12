<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Nova Reserva - Escolher Sala e Data';
?>

<div class="reservation-escolher">
    <h1 class="text-center mb-5">Nova Reserva</h1>

    <div class="row">
        <?php foreach ($salas as $sala): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h4><?= Html::encode($sala->nome_sala) ?></h4>
                        <p class="text-muted">
                            <?= $sala->capacidade ?> pessoas •
                            Preço/hora: <?= $sala->getAttribute('preco_hora') ? Yii::$app->formatter->asCurrency($sala->getAttribute('preco_hora'), 'EUR') : 'Grátis' ?> </p>
                        <p>
                            <?= Html::a('Escolher esta sala →', ['create', 'room_id' => $sala->id], [
                                'class' => 'btn btn-success btn-lg'
                            ]) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <?= Html::a('Voltar', ['site/index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>