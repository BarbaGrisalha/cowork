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
        $clientesFuturos = Reservation::find()
            ->where(['IN', 'status', ['confirmada', 'Confirmado', 'pendente']])
            ->andWhere(['>=', 'hora_inicio_agendada', date('Y-m-d H:i:s')])
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
        $faturamentoGrafico = Yii::$app->db->createCommand("
        SELECT 
            DATE_FORMAT(p.data_pagamento, '%b %Y') AS mes,
            COALESCE(SUM(p.valor), 0) AS total
        FROM payments p
        WHERE LOWER(p.status) = 'aprovado'
          AND p.data_pagamento >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
        GROUP BY DATE_FORMAT(p.data_pagamento, '%Y-%m')
        ORDER BY p.data_pagamento
    ")->queryAll();

        $mesesGrafico = array_column($faturamentoGrafico, 'mes') ?: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $valoresGrafico = array_column($faturamentoGrafico, 'total') ?: array_fill(0, 12, 0);

        // GRÁFICO 2: Top 5 salas
        $topSalas = Yii::$app->db->createCommand("
        SELECT 
     r.nome_sala,
     COUNT(res.id) AS reservas,
     COALESCE(SUM(p.valor), 0) AS faturado
 FROM rooms r
 LEFT JOIN reservations res ON res.room_id = r.id
     AND MONTH(res.hora_inicio_agendada) = MONTH(CURDATE())
     AND YEAR(res.hora_inicio_agendada) = YEAR(CURDATE())
     AND res.status IN ('confirmada', 'Confirmado', 'concluida')
 LEFT JOIN payments p ON p.reservation_id = res.id AND LOWER(p.status) = 'aprovado'
 GROUP BY r.id, r.nome_sala
 ORDER BY faturado DESC
 LIMIT 5
    ")->queryAll();

        // GRÁFICO 3: Top 5 clientes
        $topClientes = Yii::$app->db->createCommand("
        SELECT 
            c.nome,
            COALESCE(SUM(p.valor), 0) AS total
        FROM customers c
        LEFT JOIN reservations res ON res.customer_id = c.id
        LEFT JOIN payments p ON p.reservation_id = res.id AND LOWER(p.status) = 'aprovado'
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

            // DADOS DOS GRÁFICOS
            'mesesGrafico'      => $mesesGrafico,
            'valoresGrafico'    => $valoresGrafico,
            'topSalas'          => $topSalas,
            'topClientes'       => $topClientes,
        ]);
    }
}
