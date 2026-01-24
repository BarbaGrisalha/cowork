<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;
use common\models\Customer;
use common\models\Reservation;
use common\models\Payment;
use common\models\Room;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class RelatorioController extends Controller
{
    public function actionClientesMesAtual($mes = null)
    {
        if (!$mes) {
            $mes = date('Y-m');
        }

        $inicio = $mes . '-01 00:00:00';
        $fim    = date('Y-m-t 23:59:59', strtotime($inicio));

        $sql = "
        SELECT 
            c.id,
            c.nome,
            GROUP_CONCAT(DISTINCT r.nome_sala ORDER BY r.nome_sala SEPARATOR ', ') AS salas_ocupadas,
            COUNT(res.id) AS num_reservas,
            COALESCE(SUM(p.valor), 0) AS total_pago
        FROM payments p
        INNER JOIN reservations res ON res.id = p.reservation_id
        INNER JOIN customers c ON c.id = res.customer_id
        INNER JOIN rooms r ON r.id = res.room_id
        WHERE LOWER(p.status) = 'aprovado'
        AND p.valor > 0
        AND p.data_pagamento >= :inicio
        AND p.data_pagamento <= :fim
        GROUP BY c.id, c.nome
        ORDER BY total_pago DESC, c.nome ASC
        ";

        $dados = Yii::$app->db->createCommand($sql)
            ->bindValue(':inicio', $inicio)
            ->bindValue(':fim', $fim)
            ->queryAll();

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $dados,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => ['nome', 'salas_ocupadas', 'num_reservas', 'total_pago'],
                'defaultOrder' => ['total_pago' => SORT_DESC],
            ],
        ]);

        return $this->render('clientes-mes-atual', [
            'dataProvider' => $dataProvider,
            'mes' => $mes,
        ]);
    }
    public function actionClientesProximosMeses()
    {
        // Data de corte: início do mês seguinte
        $hoje = date('Y-m');
        $proximoMes = date('Y-m', strtotime('+1 month'));
        $inicio = $proximoMes . '-01 00:00:00';
        $fim = date('Y-m-t 23:59:59', strtotime($inicio . ' last day of +1 year')); // até 1 ano à frente (ajuste se quiser)

        $query = (new Query())
            ->select([
                'c.nome AS cliente_nome',
                'res.reservation_code',
                'r.nome_sala',
                'res.hora_inicio_agendada AS inicio',
                'res.hora_fim_agendada AS fim',
            ])
            ->from('reservations res')
            ->innerJoin('customers c', 'c.id = res.customer_id')
            ->innerJoin('rooms r', 'r.id = res.room_id')
            ->where(['>=', 'res.hora_inicio_agendada', $inicio])
            ->orderBy(['res.hora_inicio_agendada' => SORT_ASC]);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->all(),
            'pagination' => ['pageSize' => 50],
            'sort' => ['attributes' => ['cliente_nome', 'reservation_code', 'nome_sala', 'inicio']],
        ]);

        return $this->render('clientes-futuros', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSalasMaisAlugadas($mes = null)
    {
        if (!$mes) $mes = date('Y-m');
        $inicio = "$mes-01 00:00:00";
        $fim    = date('Y-m-t 23:59:59', strtotime($inicio));

        $sql = "
    SELECT 
        r.nome_sala, 
        COUNT(res.id) AS total_reservas, 
        COALESCE(SUM(p.valor), 0) AS valor_total 
    FROM rooms r 
    LEFT JOIN reservations res ON res.room_id = r.id 
        AND res.hora_inicio_agendada >= :inicio 
        AND res.hora_fim_agendada <= :fim 
    LEFT JOIN payments p ON p.reservation_id = res.id 
        AND LOWER(p.status) = 'aprovado' 
        AND p.valor > 0 
    GROUP BY r.id, r.nome_sala 
    HAVING total_reservas > 0 OR valor_total > 0 
    ORDER BY valor_total DESC, total_reservas DESC
    ";

        $results = Yii::$app->db->createCommand($sql)
            ->bindValue(':inicio', $inicio)
            ->bindValue(':fim', $fim)
            ->queryAll();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $results,
            'pagination' => false,
        ]);

        return $this->render('salas-ranking', [
            'dataProvider' => $dataProvider,
            'mes' => $mes,
        ]);
    }
    public function actionReservasSalas($mes = null)
    {
        if (!$mes || !preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $mes = date('Y-m');
        }

        $ano    = substr($mes, 0, 4);
        $mesNum = substr($mes, 5, 2);

        // Filtros
        $request    = Yii::$app->request;
        $salaId     = $request->get('sala', '');
        $tipo       = $request->get('tipo', '');
        $statusRes  = $request->get('status_reserva', '');
        $faturado   = $request->get('faturado', '');

        $sql = "
            SELECT 
                r.id AS room_id,
                r.nome_sala,
                c.nome AS cliente_nome,
                res.hora_inicio_agendada,
                res.hora_fim_agendada,
                res.tipo_reserva,
                res.status AS reserva_status,
                p.valor AS valor_pago
            FROM rooms r
            INNER JOIN reservations res ON res.room_id = r.id
                AND YEAR(res.hora_inicio_agendada) = :ano
                AND MONTH(res.hora_inicio_agendada) = :mes
            INNER JOIN payments p ON p.reservation_id = res.id 
                AND LOWER(p.status) = 'aprovado'
                AND p.valor > 0
            LEFT JOIN customers c ON c.id = res.customer_id
            WHERE 1 = 1
            ";

        $params = [':ano' => $ano, ':mes' => $mesNum];

        if ($salaId) {
            $sql .= " AND r.id = :salaId";
            $params[':salaId'] = $salaId;
        }
        if ($tipo) {
            $sql .= " AND res.tipo_reserva = :tipo";
            $params[':tipo'] = $tipo;
        }
        if ($statusRes) {
            $sql .= " AND res.status = :statusRes";
            $params[':statusRes'] = $statusRes;
        }

        $sql .= " ORDER BY r.nome_sala ASC, res.hora_inicio_agendada ASC";

        $dados = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // CÁLCULO DO TOTAL POR SALA
        $porSala = [];
        $totaisPorSala = [];

        foreach ($dados as $row) {
            $sala = $row['nome_sala'];
            $porSala[$sala][] = $row;
            $totaisPorSala[$sala] = ($totaisPorSala[$sala] ?? 0) + $row['valor_pago'];
        }

        // Lista de salas para o filtro
        $salasList = \common\models\Rooms::find()
            ->select(['id', 'nome_sala'])
            ->orderBy('nome_sala')
            ->asArray()
            ->all();
        $salasList = ArrayHelper::map($salasList, 'id', 'nome_sala');

        return $this->render('reservas-salas', [
            'porSala'       => $porSala,
            'totaisPorSala' => $totaisPorSala,
            'mes'           => $mes,
            'salasList'     => $salasList,
            'filters'       => [
                'sala'           => $salaId,
                'tipo'           => $tipo,
                'status_reserva' => $statusRes,
                'faturado'       => $faturado,
            ]
        ]);
    }

    public function actionSalasRanking($mes = null, $ordem = 'desc')
    {
        if (!$mes) $mes = date('Y-m');
        $inicio = "$mes-01 00:00:00";
        $fim    = date('Y-m-t 23:59:59', strtotime($inicio));

        $sql = "
            SELECT 
                r.nome_sala,
                COUNT(res.id) AS total_reservas,
                COALESCE(SUM(p.valor), 0) AS valor_total
            FROM rooms r
            LEFT JOIN reservations res ON res.room_id = r.id
                AND res.hora_inicio_agendada >= :inicio
                AND res.hora_fim_agendada <= :fim
            LEFT JOIN payments p ON p.reservation_id = res.id 
                AND LOWER(p.status) = 'aprovado'
                AND p.valor > 0
            GROUP BY r.id, r.nome_sala
            HAVING valor_total > 0
            ORDER BY valor_total DESC, total_reservas DESC
            ";

        $results = Yii::$app->db->createCommand($sql)
            ->bindValue(':inicio', $inicio)
            ->bindValue(':fim', $fim)
            ->queryAll();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $results,
            'pagination' => false,
        ]);

        return $this->render('salas-ranking', [
            'dataProvider' => $dataProvider,
            'mes' => $mes,
        ]);
    }

    public function actionReservasPendentes()
    {
        $request = Yii::$app->request;

        // Filtro por sala
        $salaId = $request->get('sala');

        // Processar cancelamento
        if ($request->isPost && $request->post('selection')) {
            $ids = $request->post('selection');
            Yii::$app->db->createCommand()
                ->update('reservations', ['status' => 'cancelada'], ['id' => $ids])
                ->execute();

            Yii::$app->session->setFlash('success', count($ids) . ' reserva(s) cancelada(s) com sucesso!');
            return $this->refresh();
        }

        $query = \common\models\Reservation::find()
            ->where(['status' => 'pendente'])
            ->with('room', 'customer');

        if ($salaId) {
            $query->andWhere(['room_id' => $salaId]);
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['hora_inicio_agendada' => SORT_ASC]],
            'pagination' => ['pageSize' => 50],
        ]);

        // Lista de salas para filtro
        $salasList = \common\models\Rooms::find()
            ->select(['id', 'nome_sala'])
            ->orderBy('nome_sala')
            ->asArray()
            ->all();
        $salasList = ArrayHelper::map($salasList, 'id', 'nome_sala');

        return $this->render('reservas-pendentes', [
            'dataProvider' => $dataProvider,
            'salasList'    => $salasList,
            'salaId'       => $salaId,
        ]);
    }

    /**
     * Relatório: Reservas do Mês (Planejado)
     * Mostra TODAS as reservas do mês (passadas, hoje e futuras)
     * Com valor pago e pendente
     */
    public function actionReservasMesPlanejado($mes = null)
    {
        if (!$mes) {
            $mes = date('Y-m');
        }

        $inicio = $mes . '-01 00:00:00';
        $fim    = date('Y-m-t 23:59:59', strtotime($inicio));

        $sql = "
        SELECT 
            c.id AS cliente_id,
            c.nome AS cliente_nome,
            GROUP_CONCAT(DISTINCT r.nome_sala ORDER BY r.nome_sala SEPARATOR ', ') AS salas,
            COUNT(res.id) AS total_reservas,
            COALESCE(SUM(p.valor), 0) AS valor_pago,
            COALESCE(SUM(res.total_estimado), 0) AS valor_previsto,
            (COALESCE(SUM(res.total_estimado), 0) - COALESCE(SUM(p.valor), 0)) AS valor_pendente
        FROM customers c
        INNER JOIN reservations res ON res.customer_id = c.id
            AND res.hora_inicio_agendada >= :inicio
            AND res.hora_inicio_agendada <= :fim
            AND LOWER(res.status) IN ('confirmada', 'Confirmado', 'concluida', 'pendente')
        INNER JOIN rooms r ON r.id = res.room_id
        LEFT JOIN payments p ON p.reservation_id = res.id 
            AND LOWER(p.status) = 'aprovado'
        GROUP BY c.id, c.nome
        HAVING total_reservas > 0
        ORDER BY valor_previsto DESC, c.nome ASC
        ";

        $dados = Yii::$app->db->createCommand($sql)
            ->bindValue(':inicio', $inicio)
            ->bindValue(':fim', $fim)
            ->queryAll();

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $dados,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => ['cliente_nome', 'total_reservas', 'valor_previsto', 'valor_pago', 'valor_pendente'],
                'defaultOrder' => ['valor_previsto' => SORT_DESC],
            ],
        ]);

        // Totais gerais do mês
        $totalGeralPrevisto = array_sum(array_column($dados, 'valor_previsto'));
        $totalGeralPago     = array_sum(array_column($dados, 'valor_pago'));
        $totalGeralPendente = $totalGeralPrevisto - $totalGeralPago;

        return $this->render('reservas-mes-planejado', [
            'dataProvider'       => $dataProvider,
            'mes'                => $mes,
            'mesBonito'          => Yii::$app->formatter->asDate($mes . '-01', 'MMMM yyyy'),
            'totalGeralPrevisto' => $totalGeralPrevisto,
            'totalGeralPago'     => $totalGeralPago,
            'totalGeralPendente' => $totalGeralPendente,
        ]);
    }
}
