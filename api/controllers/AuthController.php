<?php

namespace api\controllers;

use Yii;
use yii\rest\Controller;
use common\models\User;
use yii\web\BadRequestHttpException;

class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']); //"removi o autenticador herdado, pra evitar o boolen de sim ou nao.
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'login' => ['post'],
            ],
        ];
        // $behaviors['authenticator'] = false;
        return $behaviors;
    }

    public function actionLogin()
    {
        $data = Yii::$app->request->post();
        $email = $data['username'] ?? null; // $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Email e senha obrigatórios'];
        }

        $user = User::findOne(['username' => $email]); //$user = User::findOne(['email' => $email]);

        if (!$user || !$user->validatePassword($password)) {
            Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Credenciais inválidas'];
        }

        Yii::$app->response->statusCode = 200;
        return [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'nome' => $user->nome ?? $user->username,
                'email' => $user->email
            ]
        ];
    }
}
