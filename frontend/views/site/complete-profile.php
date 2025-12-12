<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Complete seu Perfil';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                <h3>Complete seu Perfil</h3>
            </div>
            <div class="card-body">
                <p>Para começar a reservar, precisamos dos seus dados fiscais.</p>

                <?php $form = ActiveForm::begin(); ?>

                <?= $form->field($model, 'nome')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'nif')->textInput(['placeholder' => 'Ex: 123456789']) ?>
                <?= $form->field($model, 'morada')->textarea(['rows' => 3]) ?>
                <?= $form->field($model, 'telefone')->textInput(['maxlength' => true]) ?>

                <div class="text-center">
                    <?= Html::submitButton('Salvar e Continuar →', ['class' => 'btn btn-success btn-lg']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>