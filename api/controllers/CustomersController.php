<?php

namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use common\models\Customers;
use yii\filters\auth\HttpBearerAuth;

class CustomersController extends ActiveController
{
    public $modelClass = Customers::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'header' => 'Authorization',
            'pattern' => '/^Bearer\s+(.*)$/',
            'realm' => 'API',  // opcional
        ];

        // Log para confirmar carregamento
        Yii::info("Authenticator configurado com pattern: " . $behaviors['authenticator']['pattern'], __METHOD__);

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Só customiza se necessário (ex: desativa update padrão se quiseres actionUpdate custom)
        // unset($actions['update']);  // opcional

        return $actions;
    }

    /**
     * Atualiza o perfil do cliente logado (PUT /api/web/customers/update)
     */
    public function actionUpdate()
    {
        Yii::info("actionUpdate chamado | User guest? " . (Yii::$app->user->isGuest ? 'SIM' : 'NÃO'), __METHOD__);
        Yii::info("User ID: " . (Yii::$app->user->isGuest ? 'N/A' : Yii::$app->user->id), __METHOD__);
        Yii::info("Header Authorization recebido: " . Yii::$app->request->getHeaders()->get('Authorization'), __METHOD__);

        $user = Yii::$app->user->identity;
        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Não autorizado - token inválido'];
        }

        $customer = Customers::findOne(['user_id' => $user->id]);
        if (!$customer) {
            Yii::$app->response->statusCode = 404;
            return ['success' => false, 'message' => 'Cliente não encontrado'];
        }

        $data = Yii::$app->request->post();

        if (isset($data['nome']))     $customer->nome     = $data['nome'];
        if (isset($data['telefone'])) $customer->telefone = $data['telefone'];
        if (isset($data['morada']))   $customer->morada   = $data['morada'];

        if ($customer->save()) {
            return [
                'success' => true,
                'message' => 'Perfil atualizado',
                'customer' => $customer->attributes
            ];
        }

        Yii::$app->response->statusCode = 422;
        return ['success' => false, 'errors' => $customer->getErrors()];
    }
}
