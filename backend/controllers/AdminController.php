<?php

namespace backend\controllers;

use yii\base\Controller;
use yii\filters\AccessControl;

/**
 * DashboardController (ou AdminController)
 * Protege todas as actions com base na autenticação do usuário.
 */
class AdminController extends Controller
{
    /**
     * Define o controle de acesso para todas as actions deste Controller.
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        //Regras para gerir esse controller
                        //'actions' => ['index', 'users', 'reports'], //todas as actions do admin
                        'allow' => true,
                        'roles' => ['@'], //Apenas os logados

                    ],
                ],
            ],
        ];
    }
    /**
     * Action Index - Dashboard do BackOffice
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
