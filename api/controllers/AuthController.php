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
        unset($behaviors['authenticator']); // Mantido como estava: remove autenticação herdada para login/register
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'login'    => ['post'],
                'register' => ['post'],
            ],
        ];
        return $behaviors;
    }

    /**
     * Registo de novo utilizador + customer associado
     */
    public function actionRegister()
    {
        $data = Yii::$app->request->post();

        if (empty($data['email']) || empty($data['password']) || empty($data['nome'])) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Campos email, password e nome são obrigatórios'];
        }

        $user = new User();
        $user->username = $data['email'];
        $user->email    = $data['email'];
        $user->setPassword($data['password']);
        $user->generateAuthKey();
        $user->status   = User::STATUS_ACTIVE; // Ativa logo (ajusta se quiseres verificação por email)

        if (!$user->save()) {
            Yii::$app->response->statusCode = 422;
            return ['success' => false, 'errors' => $user->getErrors()];
        }

        // Cria customer associado
        $customer = new Customers();
        $customer->user_id = $user->id;
        $customer->nome    = $data['nome'];
        // Outros campos opcionais (adiciona se existirem no form)
        // $customer->nif      = $data['nif'] ?? null;
        // $customer->telefone = $data['telefone'] ?? null;
        // $customer->morada   = $data['morada'] ?? null;

        if (!$customer->save()) {
            // Opcional: apagar user se customer falhar
            $user->delete();
            Yii::$app->response->statusCode = 422;
            return ['success' => false, 'errors' => $customer->getErrors()];
        }

        return [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso',
            'user' => [
                'id'           => $user->id,
                'nome'         => $customer->nome,
                'email'        => $user->email,
                'customer_id'  => $customer->id
            ]
            // Não retornamos token no register (pode-se adicionar se quiseres auto-login)
        ];
    }

    /**
     * Login + geração de token de acesso
     */
    public function actionLogin()
    {
        $data = Yii::$app->request->post();
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Username/email e senha obrigatórios'];
        }

        $user = User::find()
            ->where(['or', ['username' => $username], ['email' => $username]])
            ->one();

        if (!$user || !$user->validatePassword($password)) {
            Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Credenciais inválidas'];
        }

        $customer = Customers::findOne(['user_id' => $user->id]);

        // Gera token novo
        $token = Yii::$app->security->generateRandomString(64);

        Yii::info("Login OK para user ID {$user->id} | Gerado token: '$token'", __METHOD__);

        // Tenta atualizar o token
        $user->access_token = $token;

        if ($user->validate()) {  // valida primeiro
            Yii::info("Validação OK para save do token", __METHOD__);
            if ($user->save()) {
                Yii::info("Token gravado com sucesso: '$token' (ID {$user->id})", __METHOD__);
            } else {
                Yii::error("Falha no save do token. Erros: " . print_r($user->errors, true), __METHOD__);
            }
        } else {
            Yii::error("Validação falhou antes do save do token. Erros: " . print_r($user->errors, true), __METHOD__);
        }

        // Retorna SEMPRE o token gerado (mesmo se não salvou)
        return [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'nome' => $customer ? $customer->nome : $user->username,
                'email' => $user->email,
                'customer_id' => $customer ? $customer->id : null
            ],
            'token' => $token
        ];
    }
}
