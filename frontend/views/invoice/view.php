<?php

/** @var yii\web\View $this */
/** @var common\models\Reservation $reserva */ // Variável passada do seu InvoiceController

use yii\helpers\Html;
use chillerlan\QRCode\QRCode;


$this->title = 'Fatura da Reserva ' . $reserva->id;

// --- Mapeamento de Dados da Reserva para a Fatura ---
// Fatura (usamos o ID da Reserva como número de fatura para laboratório)
$invoiceNumber = 'R/' . $reserva->id;

// CORREÇÃO AQUI: Usando 'data_reserva' em vez de 'date_created'
$dateCreated = $reserva->data_reserva;

$totalValue = $reserva->total_estimado; // Usando total_estimado, que existe na sua tabela reservations

// Emitente (Dados de Laboratório)
$sellerNif = '999999990';
$sellerName = 'COWORK PROJECT - LAB';
$atcud = "LAB-12345"; // Código ATCUD fictício
$seriesValidationCode = "ABCDEF"; // Código de validação fictício

// Cliente (acessado via relação $reserva->customer)
$customer = $reserva->customer;

// Itens da Fatura
$items = [];
$subTotal = 0;
$totalIVA = 0;
$ivaRate = 23; // Exemplo de taxa padrão, ajuste conforme necessário

// 1. Item Principal: Reserva do Quarto/Espaço
// NOTA: Você deve ajustar o acesso ao preço se 'pricingPlan' não for uma relação direta
// ou se o preço base for $reserva->total_estimado (para 1 item)
$roomPrice = $reserva->total_estimado; // Simplificado para usar o total estimado como base

// Se você tiver a relação Room->PricingPlan, use:
// $roomPrice = $reserva->room->pricingPlan->valor; 

$items[] = [
    'description' => 'Reserva de Sala/Posto: ' . Html::encode($reserva->room->nome_sala),
    'quantity' => 1,
    'unit_price' => $roomPrice,
    'iva_rate' => $ivaRate,
    'line_total' => $roomPrice
];

// 2. Itens Adicionais (economato, etc.) - Se a relação existir (reservationItems)
if (isset($reserva->reservationItems)) {
    foreach ($reserva->reservationItems as $reservaItem) {
        // Assumindo que a relação 'item' (para Economato) e 'preco_venda' existem
        $itemTotal = $reservaItem->quantidade * $reservaItem->item->preco_venda;
        $items[] = [
            'description' => Html::encode($reservaItem->item->nome_item),
            'quantity' => $reservaItem->quantidade,
            'unit_price' => $reservaItem->item->preco_venda,
            'iva_rate' => $ivaRate,
            'line_total' => $itemTotal
        ];
    }
}

// Recálculo dos totais
foreach ($items as $item) {
    // Para fins de laboratório e consistência, vou usar o 'line_total' como base tributável
    // Assumindo que o line_total JÁ é o preço base sem IVA.
    $subTotal += $item['line_total'];
    $totalIVA += $item['line_total'] * ($item['iva_rate'] / 100);
}
$total = $subTotal + $totalIVA;


// --- Geração do QR Code Simplificado (Laboratório) ---
// Conteúdo: Nº Fatura|Data|Valor Total|NIF do Emitente
$qrCodeData = implode('|', [
    $invoiceNumber,
    Yii::$app->formatter->asDate($dateCreated, 'php:Ymd'),
    number_format($total, 2, '.', ''),
    $sellerNif
]);

$qrCodeImage = '';
try {
    $qrCodeImage = (new QRCode())->render($qrCodeData);
} catch (\Exception $e) {
    // Em caso de erro
}
?>

<div class="invoice-view" style="font-family: Arial, sans-serif; font-size: 10pt; padding: 20px;">

    <h1 style="text-align: center; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">
        FATURA (LAB)
    </h1>
    <p style="text-align: right; font-size: 12pt; font-weight: bold;">
        Nº: <?= Html::encode($invoiceNumber) ?>
    </p>

    <div class="header-details" style="display: flex; justify-content: space-between; margin-bottom: 30px;">

        <div class="seller-details" style="width: 45%;">
            <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 0;">Emitente</h3>
            <p><strong><?= Html::encode($sellerName) ?></strong></p>
            <p>[Endereço Fictício]</p>
            <p>NIF: <?= Html::encode($sellerNif) ?></p>
        </div>

        <div class="customer-details" style="width: 45%;">
            <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 0;">Cliente</h3>
            <p><strong><?= Html::encode($customer->nome) ?></strong></p>
            <p><?= Html::encode($customer->morada) ?></p>
            <p>NIF: <?= Html::encode($customer->nif) ?></p>
        </div>
    </div>

    <div class="invoice-info" style="margin-bottom: 20px; border-top: 1px solid #ccc; padding-top: 10px;">
        <p><strong>Data de Emissão:</strong> <?= Yii::$app->formatter->asDate($dateCreated, 'php:d-m-Y H:i:s') ?></p>
        <p><strong>Reserva Período:</strong> <?= Yii::$app->formatter->asDatetime($reserva->hora_inicio_agendada) ?> a <?= Yii::$app->formatter->asDatetime($reserva->hora_fim_agendada) ?></p>
    </div>

    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 20px;">Detalhes dos Serviços/Produtos</h3>
    <table class="table-items" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Descrição</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: center; width: 8%;">Qtd.</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 12%;">Preço Unit. (s/IVA)</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 10%;">Taxa IVA</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 15%;">Total Líquido (s/IVA)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;"><?= Html::encode($item['description']) ?></td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?= Html::encode($item['quantity']) ?></td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?= Yii::$app->formatter->asCurrency($item['unit_price']) ?></td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?= Html::encode($item['iva_rate']) ?>%</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?= Yii::$app->formatter->asCurrency($item['line_total']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-area" style="width: 40%; float: right; border: 1px solid #ccc; padding: 10px;">
        <table style="width: 100%;">
            <tr>
                <td style="padding: 5px;">Subtotal (Base Tributável):</td>
                <td style="text-align: right; font-weight: bold;"><?= Yii::$app->formatter->asCurrency($subTotal) ?></td>
            </tr>
            <tr>
                <td style="padding: 5px;">Total IVA (23%):</td>
                <td style="text-align: right; font-weight: bold;"><?= Yii::$app->formatter->asCurrency($totalIVA) ?></td>
            </tr>
            <tr style="background-color: #e0f0ff;">
                <td style="padding: 8px; font-size: 12pt; font-weight: bold;">TOTAL A PAGAR (IVA Incluído):</td>
                <td style="text-align: right; font-size: 12pt; font-weight: bold;"><?= Yii::$app->formatter->asCurrency($total) ?></td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>

    <div class="fiscal-details" style="margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc;">
        <h4 style="color: #555;">Informações (Laboratório)</h4>

        <p><strong>ATCUD:</strong> <?= Html::encode($atcud) ?> <span style="font-size: 8pt;">(Código Único de Documento Fictício)</span></p>
        <p><strong>Código de Validação da Série:</strong> <?= Html::encode($seriesValidationCode) ?></p>

        <?php if ($qrCodeImage): ?>
            <?= Html::img($qrCodeImage, ['style' => 'width: 120px; height: 120px; float: right; margin-left: 20px;']) ?>
        <?php else: ?>
            <p style='color: red;'>QR Code não gerado. Verifique a instalação da biblioteca `chillerlan/php-qrcode`.</p>
        <?php endif; ?>

        <p>Documento gerado em contexto de laboratório. O QR Code contém: Nº Fatura, Data, Valor Total e NIF do Emitente.</p>
        <p style="clear: both;"></p>
    </div>

</div>

<div style="text-align: center; margin-top: 30px;">
    <?= Html::a('Imprimir Fatura', 'javascript:window.print()', ['class' => 'btn btn-primary']) ?>
</div>