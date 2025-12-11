<?php

namespace common\rbac;

use yii\rbac\Rule;
use yii\helpers\ArrayHelper;

class OwnerRule extends Rule
{
    public $name = 'isOwner';

    public function execute($user, $item, $params)
    {
        // Se for admin, pode tudo
        if (Yii::$app->user->can('gerenciarTudo')) {
            return true;
        }

        // Se não tem modelo ou não tem customer_id → nega
        if (!isset($params['model']) || !$params['model'] instanceof \common\models\Reservation) {
            return false;
        }

        return $params['model']->customer_id == $user;
    }
}
