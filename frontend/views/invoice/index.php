<h1>Minhas Faturas</h1>
<?php

use yii\helpers\Html;
use yii\grid\GridView;

?>
<?php if ($reservasPagas): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Data</th>
                <th>Código</th>
                <th>Espaço</th>
                <th>Valor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservasPagas as $r): ?>
                <tr>
                    <td><?= Yii::$app->formatter->asDate($r->hora_inicio_agendada, 'dd/MM/yyyy') ?></td>
                    <td><strong><?= $r->reservation_code ?></strong></td>
                    <td><?= $r->room->nome_sala ?></td>
                    <td>€ <?= number_format($r->total_estimado, 2) ?></td>
                    <td>
                        <?= Html::a('Ver Fatura', ['view', 'id' => $r->id], ['class' => 'btn btn-sm btn-primary']) ?>
                     
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Ainda não tens faturas emitidas.</p>
<?php endif; ?>