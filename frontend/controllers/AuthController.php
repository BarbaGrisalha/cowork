<?php

namespace frontend\controllers;

use yii\web\Controller;

class AuthController extends Controller
{
    /**
     * Exibe a página de login específica do portal.
     * @return string
     */
    public function actionPortal()
    {
        // Aqui você coloca a lógica da sua nova tela de login (Model, etc.)
        // Ou simplesmente renderiza a view customizada.

        return $this->render('portal-login');
    }
}
