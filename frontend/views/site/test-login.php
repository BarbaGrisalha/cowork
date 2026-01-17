<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Teste Login API';
?>

<h1>Teste Login API</h1>

<?php $form = ActiveForm::begin(['action' => ['/site/test-login'], 'method' => 'post']); ?>

<div class="form-group">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="admin" required>
</div>

<div class="form-group">
    <label>Senha</label>
    <input type="password" name="password" class="form-control" value="admin123" required>
</div>

<div class="form-group">
    <?= Html::submitButton('Testar Login', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php if (Yii::$app->session->hasFlash('result')): ?>
    <div class="alert alert-info">
        <pre><?= Yii::$app->session->getFlash('result') ?></pre>
    </div>
<?php endif; ?>