<?php

use yii\helpers\Html;
use yii\widgets\Pjax; // Use Pjax para agendas e listagens, mantendo a experiência de SPA

/* @var $this yii\web\View */
/* @var $barItems array */ // Dados do Bar Virtual (Economato)
/* @var $equipment array */ // Dados dos Equipamentos (Itens)
/* @var $locations array */ // Locais disponíveis
/* @var $userReservations common\models\Reservation[] */ // Reservas do usuário

$this->title = 'Painel do Cliente | Cowork';
// Use Pjax::begin() se você quiser que as listas abaixo atualizem sem recarregar a página
// Pjax::begin(['id' => 'dashboard-pjax-container']); 
?>

<div class="dashboard-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="lead">Bem-vindo(a), <?= strtoupper(Yii::$app->user->identity->username) ?>! Aqui está o resumo do seu Cowork.</p>

    <div class="row mt-4">

        <div class="col-lg-6">
            <h2>Agenda e Locação de Espaços</h2>
            <p>Selecione um espaço para agendar:</p>

            <?php
            // O LINK ESTÁ CORRETO: APONTA PARA A ROTA DO CONTROLLER E PASSA room_id
            foreach ($locations as $location) {
                echo Html::a(
                    $location->nome_sala . ' (' . $location->status . ')',
                    ['/reservation/create', 'room_id' => $location->id],
                    ['class' => 'btn btn-outline-primary m-1']
                );
            }
            ?>

            <h3 class="mt-4">Minhas Próximas Reservas</h3>
            <?php if (empty($userReservations)): ?>
                <p>Você não tem nenhuma reserva confirmada ou pendente. Vamos começar!</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($userReservations as $reservation):
                        $roomObject = $reservation->room;
                        $locationName = $roomObject ? $roomObject->nome_sala : 'Local Removido';
                    ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                **<?= Html::encode($locationName) ?>**
                                <small class="text-muted"> | De: <?= Yii::$app->formatter->asDatetime($reservation->hora_inicio_agendada) ?> - Status: <?= $reservation->status ?></small>
                            </span>
                            <?= Html::a('Cancelar', ['/reservation/cancel', 'id' => $reservation->id], [
                                'class' => 'btn btn-sm btn-danger',
                                'data' => [
                                    'confirm' => 'Tem certeza que deseja cancelar esta reserva?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="col-lg-6">

            <h2>Inventário & Consumo</h2>
            <p>Economato e Equipamentos para uso diário.</p>

            <h3 class="mt-4">Itens de Consumo (Economato)</h3>
            <?php if (empty($barItems)): ?>
                <p class="text-info">Nenhum item de consumo disponível no momento.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($barItems as $item):
                        // **Atenção:** Mude 'nome' e 'quantidade' se as propriedades do seu model Economato forem diferentes.
                    ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= Html::encode($item->nome_item ?? 'Item sem Nome') ?>
                            <span class="badge bg-primary rounded-pill">Preço €: <?= $item->preco_venda ?? 'N/A' ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h3 class="mt-4">Equipamentos Disponíveis (Itens)</h3>
            <?php if (empty($equipment)): ?>
                <p class="text-info">Nenhum equipamento listado.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($equipment as $equip):
                        // **Atenção:** Mude 'nome_equipamento' e 'status' se as propriedades do seu model Equipment forem diferentes.
                    ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= Html::encode($equip->nome_item ?? 'Equipamento sem Nome') ?>
                            <small class="badge bg-primary rounded-pill">Preço €: <?= $equip->preco_extra ?? 'Desconhecido' ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
// Pjax::end(); 
// Descomente o Pjax::begin() e Pjax::end() se quiser o carregamento assíncrono.
?>