<?php
namespace api\controllers;

use yii\rest\ActiveController;

class RoomsController extends ActiveController
{
    public $modelClass = 'common\models\Rooms';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // Adiciona CORS se não global
        $behaviors['cors'] = [ /* mesmo array */ ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        // unset se quiseres limitar
        return $actions;
    }
}