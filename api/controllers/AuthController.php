<?php

namespace api\controllers;

use Yii;
use yii\rest\Controller;
use common\models\User;
use common\models\Customers;
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
    
    public function actionRegister()
    {
        $data = Yii::$app->request->post();

        $user = new User();
        $user->username = $data['email']; // ou campo username se separado
        $user->email = $data['email'];
        $user->setPassword($data['password']);
        $user->generateAuthKey();

        if ($user->save()) {
            // Crie customer associado se necessário
            $customer = new Customers();
            $customer->user_id = $user->id;
            $customer->nome = $data['nome'];
            // outros campos
            $customer->save();

            return [
                'success' => true,
                'message' => 'Cadastro realizado',
                'user' => [
                    'id' => $user->id,
                    'nome' => $data['nome'],
                    'email' => $user->email,
                    'customer_id' => $customer->id
                ]
            ];
        }

        Yii::$app->response->statusCode = 422;
        return ['success' => false, 'errors' => $user->getErrors()];
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

        $customer = Customers::findOne(['user_id' => $user->id]);

        // Após validar usuário
        $token = Yii::$app->security->generateRandomString(64); // token simples para teste

        return [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'nome' => $user->nome ?? $user->username,
                'email' => $user->email,
                'customer_id' => $customer ? $customer->id : null
            ],
            'token' => $token  // Retorne token
        ];
    }
}
