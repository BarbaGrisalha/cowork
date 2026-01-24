<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\Rooms;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class RoomsController extends Controller
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
                            $allowedIds = [1, 2, 13]; // admin, altamir, professor
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
            'query' => Rooms::find()->orderBy('nome_sala'),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = Rooms::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Sala não encontrada.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Rooms::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Sala não encontrada.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Sala atualizada com sucesso.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
}
