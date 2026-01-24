<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Rooms */

$this->title = 'Sala: ' . $model->nome_sala;
$this->params['breadcrumbs'][] = ['label' => 'Salas/Mesas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rooms-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card shadow-sm p-4">
        <div class="row">
            <div class="col-md-6">
                <h5 class="fw-bold">ID</h5>
                <p class="text-muted"><?= Html::encode($model->id) ?></p>

                <h5 class="fw-bold mt-4">Nome da Sala</h5>
                <p class="text-muted"><?= Html::encode($model->nome_sala) ?></p>

                <h5 class="fw-bold mt-4">Capacidade</h5>
                <p class="text-muted"><?= Html::encode($model->capacidade) ?> pessoas</p>
            </div>

            <div class="col-md-6">
                <h5 class="fw-bold">Status</h5>
                <p class="text-muted">
                    <span class="badge bg-<?= $model->status === 'ativa' ? 'success' : 'danger' ?>">
                        <?= $model->status === 'ativa' ? 'Ativa' : 'Inativa' ?>
                    </span>
                </p>


            </div>
        </div>

        <div class="mt-4 text-end">
            <?= Html::a('Editar Sala', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-2']) ?>
            <?= Html::a('Voltar à Lista', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>