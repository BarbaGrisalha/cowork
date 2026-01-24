<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Rooms */

$this->title = 'Editar Sala: ' . $model->nome_sala;
$this->params['breadcrumbs'][] = ['label' => 'Salas/Mesas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nome_sala, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="rooms-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card shadow-sm p-4">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'nome_sala')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'capacidade')->textInput() ?>
        <?= $form->field($model, 'status')->dropDownList(['ativa' => 'Ativa', 'inativa' => 'Inativa']) ?>

        <div class="mt-4 text-end">
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Cancelar', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary ms-2']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>