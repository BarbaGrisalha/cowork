<?php

namespace api\controllers;

use yii\rest\Controller;

class TestController extends Controller
{
    public function actionIndex()
    {
        return [
            'success' => true,
            'message' => 'API está viva! Controller de teste funcionando perfeitamente.',
            'time' => date('Y-m-d H:i:s'),
        ];
    }
}