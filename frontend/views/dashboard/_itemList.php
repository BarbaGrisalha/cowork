<?php

use yii\helpers\Html;
/* @var $items array */
?>

<ul class="list-group">
    <?php if (empty($items)): ?>
        <li class="list-group-item">Nenhum item disponível no momento.</li>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= Html::encode($item['name']) ?>
                <span class="badge bg-success rounded-pill"><?= Yii::$app->formatter->asCurrency($item['price']) ?></span>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>