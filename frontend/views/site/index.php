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
        <h1 class="display-4">Bem-vindo, <?= ucfirst(Html::encode(Yii::$app->user->identity->username)) ?>!</h1>
        <p class="lead">Gerencie suas reservas e acompanhe seu saldo.</p>
        <?= // Html::a('Nova Reserva', ['dashboard/create'], ['class' => 'btn btn-success btn-lg']) 
        Html::a('Nova Reserva', ['dashboard/index'], ['class' => 'btn btn-success btn-lg'])
        ?>
    </div>

    <div class="body-content">

        <div class="row">

            <!-- PRÓXIMAS RESERVAS (largura 8) -->
            <div class="col-lg-8 mb-4">
                <div class="card border-left-primary shadow h-100">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="m-0 font-weight-bold">Próximas Reservas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($proximasReservas)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <tbody>
                                        <?php foreach ($proximasReservas as $res): ?>
                                            <tr>
                                                <td width="120">
                                                    <strong><?= Yii::$app->formatter->asDate($res->hora_inicio_agendada, 'dd MMM') ?></strong><br>
                                                    <small class="text-muted d-block">
                                                        <?= Yii::$app->formatter->asTime($res->hora_inicio_agendada, 'HH:mm') ?> -
                                                        <?= Yii::$app->formatter->asTime($res->hora_fim_agendada, 'HH:mm') ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong><?= Html::encode($res->room->nome_sala) ?></strong><br>
                                                    <small class="text-muted">Código: <?= Html::encode($res->reservation_code) ?></small>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($res->hasPaidPayment()): ?>
                                                        <span class="badge bg-success">Pago</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pendente</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">Nenhuma reserva agendada!</p>
                        <?php endif; ?>

                        <div class="text-end mt-3">
                            <?= Html::a('Histórico completo →', ['reservation/index'], ['class' => 'small text-muted']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SALDO PENDENTE (agora mesma largura) -->
            <div class="col-lg-4 mb-4">
                <div class="card border-left-warning shadow h-100">
                    <div class="card-header bg-warning text-dark py-3">
                        <h5 class="m-0 font-weight-bold">Saldo Pendente</h5>
                    </div>
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h2 class="display-6 fw-bold text-danger mb-3">
                            <?= Yii::$app->formatter->asCurrency($saldoPendente, 'EUR') ?>
                        </h2>
                        <?php if ($saldoPendente > 0): ?>
                            <?= Html::a('Pagar Agora', ['payment/create-pending'], ['class' => 'btn btn-danger btn-lg']) ?>
                        <?php else: ?>
                            <p class="text-success fs-4 mb-0">Tudo em dia!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>