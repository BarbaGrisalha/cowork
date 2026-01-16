<?php

namespace api\controllers;

use yii\rest\ActiveController;

class RoomsController extends ActiveController
{
    public $modelClass = 'common\models\Rooms';

    public function behaviors()
    {
        $behaviors = parent::behaviors();  // mantém os behaviors padrão do ActiveController

        $behaviors['cors'] = [  // ou 'corsFilter' se preferires nomear assim
            'class' => \yii\filters\Cors::class,   // ← ESSA LINHA É OBRIGATÓRIA!
            'cors' => [  // configurações dentro
                'Origin' => ['*'],  // ou ['http://localhost:*, http://10.0.2.2:*'] para testar no emulador
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        // unset se quiseres limitar
        return $actions;
    }
}
