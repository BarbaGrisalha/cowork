<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    // backend/controllers/SiteController.php

    public function behaviors()
    {
        return [
            // 🛑 REMOVA O FILTRO ACCESSCONTROL DAQUI! 🛑
            // A segurança será gerenciada pelo AdminController e pelo actionLogin.

            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            // Se não estiver logado, redireciona para a tela de login.
            return $this->redirect(['site/login']);
        }

        // Se estiver logado, redireciona para o novo Dashboard protegido.
        // Isso evita que o usuário logado fique na tela inicial vazia do SiteController.
        return $this->redirect(['admin/index']);
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        // 🛑 ATENÇÃO: Mudança feita aqui para usar o layout do AdminLTE 🛑
        $this->layout = 'main-login'; // Garante que o layout com o CSS/JS do AdminLTE seja usado

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }
    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
