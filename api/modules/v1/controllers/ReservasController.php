<?php

use yii\base\Controller;
use yii\web\Response;

class ReservasController extends Controller{


public function actionIndex($cliente_id) 
{
    // 1. AUTENTICAÇÃO: Obter o ID do cliente logado (MUITO IMPORTANTE!)
    // No Yii2, se você estiver usando autenticação de token/sessão:
    // $cliente_id = Yii::$app->user->identity->id;

    // 2. BUSCA NO MODEL: Seu Model (ActiveRecord) faz a query do passo 1.
    $reservas = Reserva::find()
        ->select(['data_hora_inicio', 'data_hora_fim'])
        ->where(['cliente_id' => $cliente_id])
        ->andWhere(['>=', 'data_hora_fim', date('Y-m-d H:i:s')])
        ->orderBy('data_hora_inicio ASC')
        ->asArray() // Para retornar como array e não objetos
        ->all();

    // 3. RESPONSE: O Yii2 é lindo, ele já transforma o Array em JSON.
    Yii::$app->response->format = Response::FORMAT_JSON;

    return [
        'status' => 'success',
        'data' => $reservas
    ];
}
}