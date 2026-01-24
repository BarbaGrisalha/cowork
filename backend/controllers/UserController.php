<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use common\models\Customer;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            // IDs permitidos no backend (1 = admin, 2 = altamir, 13 = professor)
                            $allowedIds = [1, 2, 13];
                            return in_array(Yii::$app->user->id, $allowedIds);
                        },
                    ],
                ],
                'denyCallback' => function () {
                    Yii::$app->session->setFlash('error', 'Acesso negado. Apenas administradores.');
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->orderBy('username'),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = User::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Utilizador não encontrado.');
        }

        $customer = Customer::findOne(['user_id' => $model->id]);

        return $this->render('view', [
            'model'    => $model,
            'customer' => $customer,  // ← passa o customer para a view
        ]);
    }
    // Adicione no backend/controllers/UserController.php (dentro da classe)

    // No backend/controllers/UserController.php - Substitua o actionUpdate inteiro

    public function actionUpdate($id)
    {
        $model = User::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Utilizador não encontrado.');
        }

        $customer = Customer::findOne(['user_id' => $model->id]);
        if (!$customer) {
            $customer = new Customer();
            $customer->user_id = $model->id;
        }

        if ($model->load(Yii::$app->request->post()) && $customer->load(Yii::$app->request->post())) {
            $isValid = $model->validate() && $customer->validate();

            if ($isValid) {
                $model->save(false);
                $customer->save(false);

                Yii::$app->session->setFlash('success', 'Dados atualizados com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model'    => $model,
            'customer' => $customer,
        ]);
    }
}
