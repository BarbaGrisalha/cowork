<?php

use yii\helpers\Html;
use yii\helpers\helpers\Url;

$this->title = 'Cowork ILeiria - Espaço de Trabalho';
?>

<div class="jumbotron text-center bg-transparent py-5">
    <h1 class="display-3 fw-bold">Cowork ILeiria</h1>
    <p class="lead">O teu espaço de trabalho flexível em Leiria</p>
    <p class="mb-4">
        Mesas partilhadas • Salas de reunião • Internet rápida • Café incluído
    </p>
    <p>
        <?= Html::a('Fazer Login', ['site/login'], ['class' => 'btn btn-primary btn-lg mx-2']) ?>
        <?= Html::a('Ver Disponibilidade', ['reservation/create'], ['class' => 'btn btn-outline-success btn-lg mx-2']) ?>
    </p>
</div>

<div class="container py-5">
    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <i class="fas fa-clock fa-3x text-primary mb-3"></i>
            <h4>Horário</h4>
            <p>Seg–Sex: 8h–20h<br>Sáb: 9h–18h</p>
        </div>
        <div class="col-md-4 mb-4">
            <i class="fas fa-map-marker-alt fa-3x text-danger mb-3"></i>
            <h4>Localização</h4>
            <p>Rua da Inovação, 123<br>Leiria</p>
        </div>
        <div class="col-md-4 mb-4">
            <i class="fas fa-euro-sign fa-3x text-success mb-3"></i>
            <h4>Preços</h4>
            <p>Desde 7€/hora<br>50€/mês hot desk</p>
        </div>
    </div>
</div>