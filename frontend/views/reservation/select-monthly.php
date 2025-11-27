<?php

use yii\helpers\Url;

$this->title = "Reserva mensal";
?>

<h2>Escolha a data inicial do plano mensal (30 dias)</h2>

<form method="POST" action="<?= Url::to(['/reservation/create-monthly']) ?>">
    <label>Data de início:</label>
    <input type="date" name="inicio" required>

    <button type="submit">Continuar</button>
</form>