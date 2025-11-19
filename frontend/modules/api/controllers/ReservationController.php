<?php

namespace frontend\modules\api\controllers;

use yii\rest\ActiveController;
use common\models\Reservation; // Model de Reserva que você acabou de gerar
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\data\ActiveDataProvider;

// O nome da classe deve terminar em 'Controller'
class ReservationController extends ActiveController
{
    // O Yii2 usará este Model para todas as ações CRUD (GET, POST, etc.)
    public $modelClass = Reservation::class;

    // Opcional: Configurar o formato de resposta para JSON
    public function init()
    {
        parent::init();
        \Yii::$app->response->format = Response::FORMAT_JSON;
    }

    // ATENÇÃO: As ações 'availability' e 'create' personalizadas
    // devem estar aqui se você as removeu do seu controller de API anterior.
    // Exemplo:

    public function actions()
    {
        $actions = parent::actions();

        // Remove a ação padrão 'create' se você a personalizou
        unset($actions['update']);

        return $actions;
    }
    /**
     * Lida com as requisições PUT/PATCH para atualizar o status da reserva.
     * Espera um parâmetro 'status' no corpo da requisição.
     * @param int $id O ID da reserva a ser atualizada.
     * @return Reservation|array O objeto Reservation atualizado ou o array de erros.
     * @throws ServerErrorHttpException Se não conseguir salvar o modelo.
     */
    public function actionUpdate($id)
    {
        // 1. Encontra a reserva pelo ID
        $model = $this->findModel($id);

        // 2. Carrega APENAS o campo 'status' do corpo da requisição (PUT/PATCH)
        // Isso impede que outros dados (como total_estimado) sejam alterados
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->hasErrors()) {
            \Yii::$app->response->setStatusCode(422); // Unprocessable Entity
            return $model->getErrors();
        }

        // 3. Salva o modelo
        if ($model->save() === false) {
            throw new ServerErrorHttpException('Falha ao atualizar o status da reserva.');
        }

        // 4. Retorna a reserva atualizada com status 200 OK (padrão)
        return $model;
    }
    /**
     * Busca todas as reservas e formata para o padrão JSON do FullCalendar.
     * @return array
     */

    public function actionEvents()
    {

        $reservations = Reservation::find()
            // Filtra por status confirmado ou rascunho (ajuste conforme a sua lógica)
            ->where(['IN', 'status', ['confirmada', 'rascunho']])
            ->all();

        // Formato necessário pelo FullCalendar:
        foreach ($reservations as $res) {
            $events[] = [
                // Usamos o campo 'title' do Model que já está formatado
                'title' => $res->title,
                // A data e hora de início e fim da reserva
                'start' => $res->hora_inicio_agendada,
                'end' => $res->hora_fim_agendada,
                // A cor para visualização no calendário (do Model)
                'backgroundColor' => $res->color,
                // ID da reserva, útil para cliques futuros
                'id' => $res->id
            ];
        }
        return $events;
    }

    // NOVO: AÇÃO PARA VERIFICAR DISPONIBILIDADE
    // **ATENÇÃO:** O nome dos parâmetros DEVE ser $resource e $date para bater com a URLRule.
    public function actionAvailability($resource, $date)
    {
        // 1. CONFIRMAÇÃO DE ROTEAMENTO: Retornamos os parâmetros para provar que a rota funcionou.
        // Se este JSON aparecer, o roteamento está 100%
        return [
            'status' => 'ok',
            'resource_id' => $resource,
            'data' => $date,
            'message' => 'Lógica de disponibilidade deve ser implementada aqui.'
        ];

        /* // 2. Lógica Real (Implementar depois de confirmar o roteamento):
        // $startOfDay = $date . ' 00:00:00';
        // $endOfDay = $date . ' 23:59:59';
        // $reservations = Reservation::find()
        //    ->where(['room_id' => $resource])
        //    ->andWhere(['between', 'hora_inicio_agendada', $startOfDay, $endOfDay])
        //    ->all();
        // return $reservations;
        */
    }
}
