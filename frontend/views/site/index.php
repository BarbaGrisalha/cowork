<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $proximasReservas \common\models\Reservation[] */
/* @var $saldoPendente float */

$this->title = 'Minha Área - Cowork ILeiria';
?>

<div class="site-index">

    <!-- CABEÇALHO DE BOAS-VINDAS -->
    <div class="jumbotron text-center bg-transparent mb-5">
        <h1 class="display-4">Bem-vindo, <?= Html::encode(Yii::$app->user->identity->username) ?>!</h1>
        <p class="lead">Gerencie suas reservas e acompanhe seu saldo.</p>
        <?= // Html::a('Nova Reserva', ['dashboard/create'], ['class' => 'btn btn-success btn-lg']) 
        Html::a('Nova Reserva', ['dashboard/index'], ['class' => 'btn btn-success btn-lg'])
        ?>
    </div>

    <div class="body-content">

        <div class="row">

            <!-- PRÓXIMAS RESERVAS -->
            <div class="col-lg-8 mb-4">
                <div class="card border-left-primary shadow h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="m-0 font-weight-bold">Próximas Reservas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($proximasReservas)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Horário</th>
                                            <th>Sala/Mesa</th>
                                            <th>Status</th>
                                            <th>Valor</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($proximasReservas as $res): ?>
                                            <tr>
                                                <td><?= Yii::$app->formatter->asDate($res->hora_inicio_agendada, 'dd/MM/yyyy') ?></td>
                                                <td><?= Yii::$app->formatter->asTime($res->hora_inicio_agendada, 'HH:mm') ?> → <?= Yii::$app->formatter->asTime($res->hora_fim_agendada, 'HH:mm') ?></td>
                                                <td><strong><?= Html::encode($res->room->nome_sala) ?></strong></td>
                                                <td>
                                                    <?php if ($res->hasPaidPayment()): ?>
                                                        <span class="badge badge-success">Pago</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Pendente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= Yii::$app->formatter->asCurrency($res->total_estimado, 'EUR') ?></td>
                                                <td>
                                                    <?= Html::a('Ver', ['reservation/view', 'id' => $res->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Nenhuma reserva agendada!!.</p>
                        <?php endif; ?>

                        <div class="text-right mt-3">
                            <?= Html::a('Ver Todas as Minhas Reservas →', ['dashboard/index'], ['class' => 'btn btn-outline-primary']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SALDO PENDENTE -->
            <div class="col-lg-4 mb-4">
                <div class="card border-left-warning shadow h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="m-0 font-weight-bold">Saldo Pendente</h5>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-danger font-weight-bold">
                            <?= Yii::$app->formatter->asCurrency($saldoPendente, 'EUR') ?>
                        </h2>
                        <?php if ($saldoPendente > 0): ?>
                            <?= Html::a('Pagar Agora', ['payment/create-pending'], ['class' => 'btn btn-danger btn-lg']) ?>
                        <?php else: ?>
                            <p class="text-success">Tudo em dia!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>