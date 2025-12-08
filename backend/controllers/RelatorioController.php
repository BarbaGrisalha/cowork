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
            COALESCE(SUM(p.valor), 0) AS total_pago
        FROM customers c
        INNER JOIN reservations res ON res.customer_id = c.id
            AND res.hora_inicio_agendada >= :inicio
            AND res.hora_fim_agendada <= :fim
            AND res.status IN ('confirmada', 'concluida')
        INNER JOIN rooms r ON r.id = res.room_id
        LEFT JOIN payments p ON p.reservation_id = res.id 
            AND LOWER(p.status) = 'aprovado'   -- ACEITA 'aprovado', 'Aprovado', 'APROVADO'...
        GROUP BY c.id, c.nome
        HAVING salas_ocupadas IS NOT NULL
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
                'attributes' => ['nome', 'salas_ocupadas', 'total_pago'],
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
        $query = (new Query())
            ->select([
                'c.nome',
                'r.nome_sala',
                'res.hora_inicio_agendada AS inicio',
                'res.hora_fim_agendada AS fim',
                'res.tipo_reserva',
                'res.status',
            ])
            ->from('reservations res')
            ->join('INNER JOIN', 'customers c', 'c.id = res.customer_id')
            ->join('INNER JOIN', 'rooms r', 'r.id = res.room_id')
            ->where(['>=', 'res.hora_inicio_agendada', date('Y-m-d H:i:s')])
            ->orderBy('res.hora_inicio_agendada ASC');

        // ====== FILTROS ======
        $request = Yii::$app->request;

        if ($nome = $request->get('nome')) {
            $query->andWhere(['like', 'c.nome', $nome]);
        }
        if ($sala = $request->get('sala')) {
            $query->andWhere(['like', 'r.nome_sala', $sala]);
        }
        if ($tipo = $request->get('tipo')) {
            $query->andWhere(['res.tipo_reserva' => $tipo]);
        }
        if ($status = $request->get('status')) {
            $query->andWhere(['res.status' => $status]);
        }
        if ($data = $request->get('data')) {
            $query->andWhere(['>=', 'res.hora_inicio_agendada', $data . ' 00:00:00']);
        }
        if ($data_fim = $request->get('data_fim')) {
            $query->andWhere(['<=', 'res.hora_fim_agendada', $data_fim . ' 23:59:59']);
        }

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $query->all(),
            'pagination' => ['pageSize' => 30],
            'sort' => ['attributes' => ['nome', 'nome_sala', 'inicio', 'tipo_reserva', 'status']],
        ]);

        return $this->render('clientes-futuros', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $request->queryParams, // pra manter os filtros preenchidos
        ]);
    }
    /* public function actionClientesProximosMeses()
    {
        $hoje = date('Y-m-d H:i:s'); // agora com hora também

        $sql = "
        SELECT 
            c.nome,
            r.nome_sala,
            res.hora_inicio_agendada AS inicio,
            res.hora_fim_agendada AS fim,
            res.tipo_reserva,
            res.status
        FROM reservations res
        JOIN customers c ON c.id = res.customer_id
        JOIN rooms r ON r.id = res.room_id
        WHERE res.hora_inicio_agendada >= :hoje
          AND res.status IN ('confirmada', 'pendente', 'concluida')
        ORDER BY res.hora_inicio_agendada ASC
       ";

        $results = Yii::$app->db->createCommand($sql)
            ->bindValue(':hoje', $hoje)
            ->queryAll();

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $results,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => ['nome', 'nome_sala', 'inicio', 'tipo_reserva'],
                'defaultOrder' => ['inicio' => SORT_ASC],
            ],
        ]);

        return $this->render('clientes-futuros', ['dataProvider' => $dataProvider]);
    }
        */
    public function actionSalasMaisAlugadas($mes = null)
    {
        if (!$mes) $mes = date('Y-m');
        $inicio = "$mes-01 00:00:00";
        $fim    = date('Y-m-t 23:59:59', strtotime($inicio));

        $sql = "
            SELECT 
                r.nome_sala,
                COUNT(res.id) as total_reservas,
                COALESCE(SUM(p.valor), 0) as valor_total
            FROM rooms r
            LEFT JOIN reservations res ON res.room_id = r.id
                AND res.hora_inicio_agendada >= :inicio
                AND res.hora_fim_agendada <= :fim
                AND res.status IN ('confirmada', 'concluida')
            LEFT JOIN payments p ON p.reservation_id = res.id AND p.status = 'aprovado'
            GROUP BY r.id, r.nome_sala
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
        $salaId     = $request->get('sala');
        $tipo       = $request->get('tipo');
        $statusRes  = $request->get('status_reserva');
        $faturado   = $request->get('faturado');

        $sql = "
        SELECT 
            r.id AS room_id,
            r.nome_sala,
            c.nome AS cliente_nome,
            res.hora_inicio_agendada,
            res.hora_fim_agendada,
            res.tipo_reserva,
            res.status AS reserva_status,
            COALESCE(p.valor, 0) AS valor_pago,
            CASE WHEN p.id IS NOT NULL AND LOWER(p.status) = 'aprovado' THEN 'pago' ELSE 'pendente' END AS situacao_pagamento
        FROM rooms r
        INNER JOIN reservations res ON res.room_id = r.id
            AND YEAR(res.hora_inicio_agendada) = :ano
            AND MONTH(res.hora_inicio_agendada) = :mes
            AND res.status IN ('confirmada', 'concluida', 'pendente')
        LEFT JOIN customers c ON c.id = res.customer_id
        LEFT JOIN payments p ON p.reservation_id = res.id AND LOWER(p.status) = 'aprovado'
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
        if ($faturado === 'pago') {
            $sql .= " AND p.id IS NOT NULL";
        } elseif ($faturado === 'pendente') {
            $sql .= " AND p.id IS NULL";
        }

        $sql .= " ORDER BY r.nome_sala ASC, res.hora_inicio_agendada ASC";

        $dados = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // === CÁLCULO DO TOTAL POR SALA ===
        $porSala = [];
        $totaisPorSala = [];

        foreach ($dados as $row) {
            $sala = $row['nome_sala'];
            $porSala[$sala][] = $row;

            if ($row['situacao_pagamento'] === 'pago') {
                $totaisPorSala[$sala] = ($totaisPorSala[$sala] ?? 0) + $row['valor_pago'];
            } else {
                $totaisPorSala[$sala] = ($totaisPorSala[$sala] ?? 0);
            }
        }

        // Lista de salas para o filtro
        $salasList = \common\models\Rooms::find()
            ->select(['id', 'nome_sala'])
            ->orderBy('nome_sala')
            ->asArray()
            ->all();
        $salasList = \yii\helpers\ArrayHelper::map($salasList, 'id', 'nome_sala');

        return $this->render('reservas-salas', [
            'porSala'       => $porSala,           // NOVO
            'totaisPorSala' => $totaisPorSala,     // NOVO
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
        if (!$mes) {
            $mes = date('Y-m');
        }

        $inicio = $mes . '-01 00:00:00';
        $fim    = date('Y-m-t 23:59:59', strtotime($inicio));

        // Força ordem válida
        $ordem = ($ordem === 'asc') ? 'ASC' : 'DESC';

        $sql = "
        SELECT 
            r.nome_sala,
            COUNT(res.id) AS total_reservas,
            COALESCE(SUM(p.valor), 0) AS valor_total
        FROM rooms r
        LEFT JOIN reservations res ON res.room_id = r.id
            AND res.hora_inicio_agendada >= :inicio
            AND res.hora_fim_agendada <= :fim
            AND res.status IN ('confirmada', 'concluida')
        LEFT JOIN payments p ON p.reservation_id = res.id 
            AND LOWER(p.status) = 'aprovado'
        GROUP BY r.id, r.nome_sala
        HAVING total_reservas > 0 OR valor_total > 0
        ORDER BY valor_total $ordem, total_reservas $ordem
        ";

        $dados = Yii::$app->db->createCommand($sql)
            ->bindValue(':inicio', $inicio)
            ->bindValue(':fim', $fim)
            ->queryAll();

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $dados,
            'pagination' => false,
        ]);

        return $this->render('salas-ranking', [
            'dataProvider' => $dataProvider,
            'mes'          => $mes,
            'ordem'        => $ordem === 'ASC' ? 'asc' : 'desc',
        ]);
    }
}
