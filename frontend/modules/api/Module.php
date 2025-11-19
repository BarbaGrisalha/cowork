<?php

namespace frontend\modules\api;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'frontend\controllers\api\v1';

    public function init()
    {
        parent::init();
    }
}
