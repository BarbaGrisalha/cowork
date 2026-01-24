<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $customer common\models\Customer */

$this->title = 'Editar Utilizador: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Utilizadores', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card shadow-sm p-4">
        <?php $form = ActiveForm::begin(); ?>

        <h5 class="fw-bold mb-3">Dados do Utilizador</h5>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

        <hr class="my-4">

        <h5 class="fw-bold mb-3">Dados do Cliente</h5>
        <?= $form->field($customer, 'nome')->textInput(['maxlength' => true]) ?>
        <?= $form->field($customer, 'nif')->textInput(['maxlength' => true]) ?>
        <?= $form->field($customer, 'morada')->textInput(['maxlength' => true]) ?>
        <?= $form->field($customer, 'telefone')->textInput(['maxlength' => true]) ?>

        <div class="mt-4 text-end">
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Cancelar', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary ms-2']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>