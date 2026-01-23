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
     * Suporta atualização de nome, telefone, morada e upload de foto de perfil
     */
    /**
     * Atualiza o perfil do cliente logado (PUT /api/web/customers/update)
     * Suporta atualização de nome, telefone, morada e upload de foto de perfil
     */
    public function actionUpdate()
    {
        Yii::info("actionUpdate chamado | User guest? " . (Yii::$app->user->isGuest ? 'SIM' : 'NÃO'), __METHOD__);
        Yii::info("User ID: " . (Yii::$app->user->isGuest ? 'N/A' : Yii::$app->user->id), __METHOD__);
        Yii::info("Header Authorization recebido: " . Yii::$app->request->getHeaders()->get('Authorization'), __METHOD__);

        // Temporário: sem auth para testar upload de foto (comente depois)
        // $user = Yii::$app->user->identity;
        // if (!$user) {
        //     Yii::$app->response->statusCode = 401;
        //     return ['success' => false, 'message' => 'Não autorizado - token inválido'];
        // }

        // Para teste: use um customer fixo (mude para o seu ID real ou pegue do token depois)
        $customer = Customers::findOne(1);  // ← ID do seu customer para teste
        if (!$customer) {
            Yii::$app->response->statusCode = 404;
            return ['success' => false, 'message' => 'Cliente não encontrado'];
        }

        // Carrega dados do POST (nome, telefone, morada)
        $data = Yii::$app->request->post();

        if (isset($data['nome']))     $customer->nome     = $data['nome'];
        if (isset($data['telefone'])) $customer->telefone = $data['telefone'];
        if (isset($data['morada']))   $customer->morada   = $data['morada'];

        // Upload de foto de perfil (multipart/form-data)
        $foto = UploadedFile::getInstanceByName('foto_perfil');
        if ($foto !== null) {
            // Validação básica
            if (!in_array(strtolower($foto->extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                Yii::$app->response->statusCode = 400;
                return ['success' => false, 'message' => 'Formato inválido (jpg, jpeg, png, gif)'];
            }
            if ($foto->size > 5 * 1024 * 1024) { // 5MB
                Yii::$app->response->statusCode = 400;
                return ['success' => false, 'message' => 'Imagem muito grande (máximo 5MB)'];
            }

            // Pasta de upload
            $uploadPath = Yii::getAlias('@webroot') . '/uploads/perfil/';
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }

            // Nome único
            $extensao = $foto->extension;
            $nomeArquivo = 'perfil_' . $customer->id . '_' . time() . '.' . $extensao;
            $caminhoCompleto = $uploadPath . $nomeArquivo;

            // Salva arquivo
            if ($foto->saveAs($caminhoCompleto)) {
                $customer->foto_perfil = '/uploads/perfil/' . $nomeArquivo;
                Yii::info("Foto salva: " . $customer->foto_perfil, __METHOD__);
            } else {
                Yii::error("Falha ao salvar foto: " . $foto->error, __METHOD__);
                Yii::$app->response->statusCode = 500;
                return ['success' => false, 'message' => 'Erro ao salvar foto'];
            }
        }

        // Salva alterações
        if ($customer->save()) {
            Yii::$app->response->statusCode = 200;
            return [
                'success' => true,
                'message' => 'Perfil atualizado (sem auth para teste)',
                'customer' => $customer->attributes
            ];
        }

        Yii::$app->response->statusCode = 422;
        return ['success' => false, 'errors' => $customer->getErrors()];
    }
}
