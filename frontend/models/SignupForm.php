<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }

    /**
     * Signs user up + atribui permissões automaticamente
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();

        // Salva o usuário primeiro
        if ($user->save()) {
            // AQUI É A MÁGICA: atribui as permissões de cliente automaticamente
            $auth = Yii::$app->authManager;

            $auth->assign($auth->getPermission('fazerReserva'), $user->id);
            $auth->assign($auth->getPermission('listarMinhasReservas'), $user->id);
            $auth->assign($auth->getPermission('verReserva'), $user->id);
            $auth->assign($auth->getPermission('atualizarReserva'), $user->id);
            $auth->assign($auth->getPermission('cancelarReserva'), $user->id);

            // O usuário foi criado e já tem todas as permissões de cliente!

            // Envia email de confirmação
            $this->sendEmail($user);

            return $user;
        }

        return null;
    }

    /**
     * Sends confirmation email to user
     */
    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}
