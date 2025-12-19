<?php

namespace frontend\modules\api\controllers;

use yii\rest\Controller;
use common\models\LoginForm;
use Yii;

class AuthController extends Controller
{
    // Isso remove a necessidade de CSRF token para a API (que causa erro 400/404 em REST)
    public $enableCsrfValidation = false;

    public function actionLogin()
    {
        $model = new LoginForm();

        // O segundo parâmetro vazio '' indica que não esperamos um prefixo no JSON
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            $user = Yii::$app->user->identity;
            return [
                'status' => 'success',
                'message' => 'Login efetuado com sucesso',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'access_token' => $user->auth_key, // O RBAC usará isso depois
                ],
            ];
        }

        Yii::$app->response->statusCode = 422; // Unprocessable Entity
        return [
            'status' => 'error',
            'errors' => $model->errors,
        ];
    }
}
