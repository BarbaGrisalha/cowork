<?php

namespace backend\controllers;

use Yii;
use yii\base\Controller;
use yii\filters\AccessControl;
use common\models\Reservation;



/**
 * DashboardController (ou AdminController)
 * Protege todas as actions com base na autenticação do usuário.
 */
class AdminController extends Controller
{
    /**
     * Só quem tem a role "admin" (ou permissão gerenciarTudo) entra aqui
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            //ids permitidos no backend.So Eu, admin e Professor.
                            $allowedIds = [1, 2, 13]; //TODO - Falta colocar aqui o professor 1-admin, 2-altamir, 13-professor
                            return in_array(Yii::$app->user->id, $allowedIds);
                        }
                    ],
                ],
                'denyCallback' => function () {
                    Yii::$app->session->setFlash('error', 'Acesso negado. Só administradores.');
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
        ];
    }
    /**
     * Action Index - Dashboard do BackOffice
     * @return string
     */
    public function actionIndex()
    {
        $mesAtual = date('Y-m');
        $mesBonito = Yii::$app->formatter->asDate($mesAtual . '-01', 'MMMM yyyy');

        // 1. Clientes Futuros
        /* $clientesFuturos = Reservation::find()
            ->where(['IN', 'status', ['confirmada', 'Confirmado', 'pendente']])
            ->andWhere(['>=', 'hora_inicio_agendada', date('Y-m-d H:i:s')])
            ->count();
*/
        $proximoMes = date('Y-m', strtotime('+1 month'));
        $inicio = $proximoMes . '-01 00:00:00';

        $clientesFuturos = Reservation::find()
            ->where(['IN', 'status', ['confirmada', 'Confirmado', 'pendente']])
            ->andWhere(['>=', 'hora_inicio_agendada', $inicio])
            ->count();
        // 2. Faturamento do mês atual
        $faturamentoMes = Yii::$app->db->createCommand("
                SELECT COALESCE(SUM(p.valor), 0)
                FROM payments p
                INNER JOIN reservations r ON p.reservation_id = r.id
                WHERE LOWER(p.status) = 'aprovado'
                AND YEAR(r.hora_inicio_agendada) = YEAR(CURDATE())
                AND MONTH(r.hora_inicio_agendada) = MONTH(CURDATE())
            ")->queryScalar();

        // 3. Total reservas do mês
        $totalReservasMes = Reservation::find()
            ->where([
                'BETWEEN',
                'hora_inicio_agendada',
                date('Y-m-01 00:00:00'),
                date('Y-m-t 23:59:59')
            ])
            ->andWhere(['IN', 'status', ['confirmada', 'Confirmado', 'concluida']])
            ->count();

        // 4. Pendentes
        $pendentes = Reservation::find()
            ->where(['status' => 'pendente'])
            ->count();

        // GRÁFICO 1: Faturamento últimos 12 meses
        $mesesGrafico = [];
        $valoresGrafico = [];

        for ($i = 11; $i >= 0; $i--) {
            $dataMes = date('Y-m', strtotime("-$i month"));
            $mesBonitoGraf = Yii::$app->formatter->asDate($dataMes . '-01', 'MMM yyyy');
            $mesesGrafico[] = $mesBonitoGraf;

            $inicio = $dataMes . '-01 00:00:00';
            $fim    = date('Y-m-t 23:59:59', strtotime($inicio));

            $total = (float) Yii::$app->db->createCommand("
            SELECT COALESCE(SUM(p.valor), 0)
            FROM payments p
            INNER JOIN reservations res ON res.id = p.reservation_id
            WHERE LOWER(p.status) = 'aprovado'
              AND p.valor > 0
              AND p.data_pagamento >= :inicio
              AND p.data_pagamento <= :fim
        ")
                ->bindValue(':inicio', $inicio)
                ->bindValue(':fim', $fim)
                ->queryScalar();

            $valoresGrafico[] = $total;
        }

        // GRÁFICO 2: Top 5 salas - Este Mês
        $inicio = $mesAtual . '-01 00:00:00';
        $fim = date('Y-m-t 23:59:59', strtotime($inicio));

        $topSalasSql = "
        SELECT 
            r.nome_sala,
            COALESCE(SUM(p.valor), 0) AS faturado
        FROM rooms r
        LEFT JOIN reservations res ON res.room_id = r.id
            AND res.hora_inicio_agendada >= :inicio
            AND res.hora_fim_agendada <= :fim
        LEFT JOIN payments p ON p.reservation_id = res.id 
            AND LOWER(p.status) = 'aprovado'
            AND p.valor > 0
        GROUP BY r.id, r.nome_sala
        ORDER BY faturado DESC, r.nome_sala ASC
        LIMIT 5
        ";

        $topSalas = Yii::$app->db->createCommand($topSalasSql)
            ->bindValue(':inicio', $inicio)
            ->bindValue(':fim', $fim)
            ->queryAll();

        // GRÁFICO 3: Top 5 clientes (Histórico)
        $topClientes = Yii::$app->db->createCommand("
        SELECT 
            c.nome,
            COALESCE(SUM(p.valor), 0) AS total
        FROM customers c
        LEFT JOIN reservations res ON res.customer_id = c.id
        LEFT JOIN payments p ON p.reservation_id = res.id 
            AND LOWER(p.status) = 'aprovado'
            AND p.valor > 0
        GROUP BY c.id, c.nome
        ORDER BY total DESC
        LIMIT 5
        ")->queryAll();

        return $this->render('index', [
            'clientesFuturos'   => $clientesFuturos,
            'faturamentoMes'    => $faturamentoMes,
            'totalReservasMes'  => $totalReservasMes,
            'pendentes'         => $pendentes,
            'mesAtual'          => $mesBonito,

            'mesesGrafico'      => $mesesGrafico,
            'valoresGrafico'    => $valoresGrafico,
            'topSalas'          => $topSalas,
            'topClientes'       => $topClientes,
        ]);
    }
}
