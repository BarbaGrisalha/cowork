<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\models\Reservation;
use frontend\models\Customer;


/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post', 'get'], //ajustei para receber get tambem
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
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Renders the imported frontend "Home" page.
     * @return string
     */
    public function actionIndex()
    {
        // SE NÃO ESTIVER LOGADO → mostra página pública
        if (Yii::$app->user->isGuest) {
            return $this->render('welcome');
        }

        // PEGA O ID DO CUSTOMER (não do user)
        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        if (!$customer) {
            // Se por algum motivo não tiver customer (impossível, mas seguro)
            Yii::$app->session->setFlash('error', 'Perfil não encontrado.');
            return $this->redirect(['site/complete-profile']);
        }

        $customerId = $customer->id;

        // PRÓXIMAS RESERVAS (hoje em diante)
        $proximasReservas = Reservation::find()
            ->joinWith('room')
            ->where(['reservations.customer_id' => $customerId])
            ->andWhere(['>=', 'hora_inicio_agendada', date('Y-m-d 00:00:00')])
            ->orderBy('hora_inicio_agendada ASC')
            ->limit(10)
            ->all();

        // SALDO PENDENTE
        $saldoPendente = Yii::$app->db->createCommand("
        SELECT COALESCE(SUM(r.total_estimado), 0)
        FROM reservations r
        LEFT JOIN payments p ON p.reservation_id = r.id AND LOWER(p.status) = 'aprovado'
        WHERE r.customer_id = :customerId
          AND r.status IN ('confirmada', 'pendente')
          AND p.id IS NULL
    ")->bindValue(':customerId', $customerId)->queryScalar();

        return $this->render('index', [
            'proximasReservas' => $proximasReservas,
            'saldoPendente'    => $saldoPendente,
        ]);
    }

    /**
     * Logs in a user.
     * Primeiro login → força completar perfil
     * Depois → vai direto pro dashboard/agenda
     */
    public function actionLogin()
    {
        // Se já tá logado → vai pro dashboard NOVO (o lindo)
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/frontend-cowork']);
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {

            // Primeiro login ou falta NIF → força completar perfil
            $customer = \frontend\models\Customer::findOne(['user_id' => Yii::$app->user->id]);

            if (!$customer || empty($customer->nif)) {
                Yii::$app->session->setFlash('info', 'Complete seu perfil para começar a reservar.');
                return $this->redirect(['site/complete-profile']);
            }

            // Já tem tudo → vai pro dashboard NOVO
            return $this->redirect(['site/frontend-cowork']);
        }

        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->verifyEmail()) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

    /**
     * Exibe a página Cowork após o login bem-sucedido.
     *
     * @return mixed
     */
    public function actionFrontendCowork()
    {
        // Esta action irá renderizar automaticamente:
        // frontend/views/site/frontendCowork.php

        // Se precisar de alguma lógica ou carregar dados, faça aqui.
        // Ex: $data = MyModel::find()->all();
        // return $this->render('frontendCowork', ['data' => $data]);

        return $this->render('frontendOffice');
    }

    /**
     * Exibe a página privacityPage após o clique
     *
     * @return string
     */
    public function actionPrivacityPage()
    {

        return $this->render('privacityPage');
    }
    /**
     * Exibe a página termsOfService após o clique
     *
     * @return string
     */
    public function actionTermsOfService()
    {

        return $this->render('termsOfService');
    }
    /**
     * Exibe a página cookiesPolicy após o clique
     *
     * @return string
     */
    public function actionCookiesPolicy()
    {

        return $this->render('cookiesPolicy');
    }

    public function actionCompleteProfile()
    {
        // Se já tem perfil completo → vai pro dashboard
        $customer = \frontend\models\Customer::findOne(['user_id' => Yii::$app->user->id]);
        if ($customer && $customer->nif && $customer->morada) {
            return $this->redirect(['site/index']);
        }

        $model = $customer ?? new \frontend\models\Customer();
        $model->user_id = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Perfil completado com sucesso! Agora pode fazer reservas.');
            return $this->redirect(['site/index']);
        }

        return $this->render('complete-profile', ['model' => $model]);
    }
}
