<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="utf-8">
    <title>Fatura - <?= $reserva->reservation_code ?></title>
    <style>
        <style>body {
            font-family: DejaVu Sans, sans-serif;
            margin: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .info {
            width: 100%;
            margin-bottom: 30px;
        }

        .info td {
            padding: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 4px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Cowork IPLeiria</h1>
        <p>Rua Example, 123<br>2410-000 Leiria<br>NIF: 512345678</p>
        <h2>FATURA</h2>
    </div>

    <table class="info">
        <tr>
            <td><strong>Cliente:</strong></td>
            <td><?= Html::encode($reserva->customer->nome ?? 'N/D') ?></td>
            <td><strong>Data:</strong></td>
            <td><?= Yii::$app->formatter->asDate($reserva->hora_inicio_agendada, 'dd/MM/yyyy') ?></td>
        </tr>
        <tr>
            <td><strong>NIF:</strong></td>
            <td><?= Html::encode($reserva->customer->nif ?? 'Consumidor Final') ?></td>
            <td><strong>Código:</strong></td>
            <td><strong><?= $reserva->reservation_code ?></strong></td>
        </tr>
        <tr>
            <td><strong>Morada:</strong></td>
            <td colspan="3"><?= Html::encode($reserva->customer->morada ?? 'N/D') ?></td>
        </tr>
    </table>

    <h3>Serviço Prestado</h3>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="text-center">Quantidade</th>
                <th class="text-right">Preço Unit.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Aluguer de <?= Html::encode($reserva->room->nome_sala) ?><br>
                    <small><?= Yii::$app->formatter->asDate($reserva->hora_inicio_agendada, 'dd/MM/yyyy HH:mm') ?> -
                        <?= Yii::$app->formatter->asTime($reserva->hora_fim_agendada, 'HH:mm') ?></small>
                </td>
                <td class="text-center">1</td>
                <td class="text-right">€ <?= number_format($reserva->total_estimado, 2) ?></td>
                <td class="text-right">€ <?= number_format($reserva->total_estimado, 2) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>€ <?= number_format($reserva->total_estimado, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <p class="text-center mt-5">
        Obrigado pela preferência!<br>
        Cowork IPLeiria — Espaço de Trabalho Colaborativo
    </p>

</body>

</html>