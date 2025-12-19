<?php

namespace frontend\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use common\models\LoginForm;

class LoginController extends Controller
{
    // Importante para aceitar POST do Insomnia sem erro de CSRF
    public $enableCsrfValidation = false;

    public function actionLogin()
    {
        $model = new LoginForm();

        // O Yii2 REST por padrão espera JSON. 
        // O segundo parâmetro '' faz o load ler o JSON bruto do Insomnia.
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            return [
                'id' => Yii::$app->user->identity->id,
                'username' => Yii::$app->user->identity->username,
                'token' => Yii::$app->user->identity->auth_key, // Use isso para o RBAC depois
            ];
        }

        Yii::$app->response->statusCode = 401;
        return [
            'errors' => $model->errors,
        ];
    }
}
