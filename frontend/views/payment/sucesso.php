<?php

use yii\helpers\Html;
use yii\bootstrap5\Modal;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $reservation common\models\Reservations */

$this->title = 'Reserva Confirmada com Sucesso!';

$reservationCode = $reservation->reservation_code; // Ex: RES-20251205-MESADECOWORKING1-42
$checkInDate = Yii::$app->formatter->asDate($reservation->hora_inicio_agendada, 'dd/MM/yyyy');
$checkInTime = Yii::$app->formatter->asTime($reservation->hora_inicio_agendada, 'HH:mm');
$checkOutTime = Yii::$app->formatter->asTime($reservation->hora_fim_agendada, 'HH:mm');

?>

<div class="pagamento-sucesso py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-success">
                Reserva Confirmada!
            </h1>
            <p class="lead text-muted">Seu espaço está garantido. Pode comemorar!</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-check-circle fa-4x text-success mb-4"></i>

                        <h2 class="h4 fw-bold">Pagamento Aprovado</h2>
                        <p class="text-muted mb-4">
                            Sua reserva foi confirmada com sucesso!<br>
                            Aqui está o seu <strong>código de reserva</strong>:
                        </p>

                        <!-- CÓDIGO DE RESERVA EM DESTAQUE -->
                        <div class="bg-light py-3 px-4 rounded-3 d-inline-block mb-4 border border-success">
                            <code class="fs-3 fw-bold text-success"><?= Html::encode($reservationCode) ?></code>
                        </div>

                        <div class="row text-start mt-4 g-4">
                            <div class="col-md-6">
                                <strong>Espaço:</strong><br>
                                <span class="text-primary"><?= Html::encode($reservation->room->nome_sala) ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Data:</strong><br>
                                <span><?= $checkInDate ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Horário:</strong><br>
                                <?= $checkInTime ?> às <?= $checkOutTime ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Valor Pago:</strong><br>
                                <span class="fs-5 fw-bold text-success">
                                    R$ <?= number_format($reservation->total_estimado, 2, ',', '.') ?>
                                </span>
                            </div>
                        </div>

                        <hr class="my-5">

                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                            <!-- BOTÃO PRINCIPAL: VER DETALHES DA RESERVA -->
                            <button type="button" class="btn btn-success btn-lg px-5" data-bs-toggle="modal" data-bs-target="#modalReserva">
                                Ver Comprovante da Reserva
                            </button>

                            <?= Html::a('Ir para o Dashboard', ['/site/index'], ['class' => 'btn btn-outline-primary btn-lg px-5']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====================== MODAL / POP-UP ====================== -->
<?php Modal::begin([
    'id' => 'modalReserva',
    'title' => '<h3 class="modal-title text-center w-100"><i class="fas fa-ticket-alt me-2"></i> Seu Comprovante de Reserva</h3>',
    'size' => Modal::SIZE_LARGE,
    'headerOptions' => ['class' => 'border-0 pb-0'],
    'bodyOptions' => ['class' => 'pt-0'],
    'centerVertical' => true,
]); ?>

<div class="text-center p-4">
    <div class="bg-white rounded-4 shadow-sm p-5 border" style="max-width: 500px; margin: 0 auto;">
        <!-- Logo opcional -->
        <div class="mb-4">
            <h2 class="text-primary fw-bold">Cowork IPLeiria</h2>
            <small class="text-muted">Espaço de Trabalho Colaborativo</small>
        </div>

        <div class="border-top border-bottom py-4 my-4">
            <p class="mb-2"><strong>Código da Reserva</strong></p>
            <h1 class="display-5 fw-bold text-success mb-3"><?= Html::encode($reservationCode) ?></h1>
        </div>

        <div class="row text-start mb-4">
            <div class="col-6"><strong>Cliente:</strong></div>
            <div class="col-6"><?= Html::encode(Yii::$app->user->identity->nome ?? 'Usuário') ?></div>

            <div class="col-6"><strong>Espaço:</strong></div>
            <div class="col-6 text-primary fw-bold"><?= Html::encode($reservation->room->nome_sala) ?></div>

            <div class="col-6"><strong>Data:</strong></div>
            <div class="col-6"><?= $checkInDate ?></div>

            <div class="col-6"><strong>Horário:</strong></div>
            <div class="col-6"><?= $checkInTime ?> - <?= $checkOutTime ?></div>

            <div class="col-6"><strong>Valor:</strong></div>
            <div class="col-6 fw-bold text-success">
                R$ <?= number_format($reservation->total_estimado, 2, ',', '.') ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-muted small">
                Apresente este comprovante na recepção ou use o código para check-in automático.
            </p>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                Imprimir Comprovante
            </button>
        </div>

        <!-- QR Code (opcional - descomente quando quiser usar) -->
        <!--
        <div class="mt-4">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($reservationCode) ?>" 
                 alt="QR Code" class="img-fluid rounded shadow">
            <p class="small text-muted mt-2">Escaneie para check-in rápido</p>
        </div>
        -->
    </div>
</div>

<?php Modal::end(); ?>

<?php
// Script para melhorar a experiência (opcional)
$this->registerJs(<<<JS
    // Fecha o modal ao clicar fora ou no X
    var modal = document.getElementById('modalReserva');
    modal.addEventListener('shown.bs.modal', function () {
        document.body.style.overflow = 'hidden';
    });
    modal.addEventListener('hidden.bs.modal', function () {
        document.body.style.overflow = 'auto';
    });
JS);
?>