<?php

namespace api\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $enableCsrfValidation = false; // pra não dar problema de CSRF

    public function actionIndex()
    {
        // Resposta simples em JSON
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'message' => 'API Yii 2 está viva e funcionando!',
            'time' => date('Y-m-d H:i:s'),
            'yii_version' => \Yii::getVersion(),
        ];
    }
}
