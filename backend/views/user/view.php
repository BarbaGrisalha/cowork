<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $customer common\models\Customer|null */

$this->title = 'Utilizador: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Utilizadores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card shadow-sm p-4">
        <div class="row">
            <div class="col-md-6">
                <h5 class="fw-bold">ID</h5>
                <p class="text-muted"><?= Html::encode($model->id) ?></p>

                <h5 class="fw-bold mt-4">Username</h5>
                <p class="text-muted"><?= Html::encode($model->username) ?></p>

                <h5 class="fw-bold mt-4">Email</h5>
                <p class="text-muted"><?= Html::encode($model->email) ?></p>

                <h5 class="fw-bold mt-4">Status</h5>
                <p class="text-muted">
                    <span class="badge bg-<?= $model->status == 10 ? 'success' : 'danger' ?>">
                        <?= $model->status == 10 ? 'Ativo' : 'Inativo' ?>
                    </span>
                </p>

                <h5 class="fw-bold mt-4">Datas</h5>
                <p class="text-muted mb-0">
                    <strong>Criado em:</strong> <?= Yii::$app->formatter->asDatetime($model->created_at) ?> &nbsp;&nbsp;&nbsp;
                    <strong>Atualizado em:</strong> <?= Yii::$app->formatter->asDatetime($model->updated_at) ?>
                </p>
            </div>

            <div class="col-md-6">
                <h5 class="fw-bold">Informações do Cliente</h5>
                <?php if ($customer): ?>
                    <p><strong>Nome:</strong> <?= Html::encode($customer->nome) ?></p>
                    <p><strong>NIF:</strong> <?= Html::encode($customer->nif) ?></p>
                    <p><strong>Morada:</strong> <?= Html::encode($customer->morada ?? '—') ?></p>
                    <p><strong>Telefone:</strong> <?= Html::encode($customer->telefone ?? '—') ?></p>
                    <p><strong>Data Registro:</strong> <?= Yii::$app->formatter->asDate($customer->data_registro) ?></p>
                <?php else: ?>
                    <p class="text-muted">Sem perfil de cliente associado.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4 text-end">
            <?= Html::a('Editar Dados', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-2']) ?>
            <?= Html::a('Voltar à Lista', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>