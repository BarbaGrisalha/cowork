<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customers".
 *
 * @property int $id
 * @property int $user_id
 * @property string $nome
 * @property string $nif
 * @property string|null $morada
 * @property string|null $telefone
 * @property string $data_registro
 * @property string $data_atualizacao
 *
 * @property CustomerCardTokens[] $customerCardTokens
 * @property Invoices[] $invoices
 * @property MbwayAccounts[] $mbwayAccounts
 * @property PaypalAccounts[] $paypalAccounts
 * @property Reservations[] $reservations
 * @property User $user
 */
class Customer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['morada', 'telefone'], 'default', 'value' => null],
            [['user_id', 'nome', 'nif'], 'required'],
            [['user_id'], 'integer'],
            [['data_registro', 'data_atualizacao'], 'safe'],
            [['nome'], 'string', 'max' => 100],
            [['nif', 'telefone'], 'string', 'max' => 20],
            [['morada'], 'string', 'max' => 255],
            [['nif'], 'unique'],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'nome' => 'Nome',
            'nif' => 'Nif',
            'morada' => 'Morada',
            'telefone' => 'Telefone',
            'data_registro' => 'Data Registro',
            'data_atualizacao' => 'Data Atualizacao',
        ];
    }

    // Relações
    public function getCustomerCardTokens()
    {
        return $this->hasMany(CustomerCardTokens::class, ['customer_id' => 'id']);
    }
    public function getInvoices()
    {
        return $this->hasMany(Invoices::class, ['customer_id' => 'id']);
    }
    public function getMbwayAccounts()
    {
        return $this->hasMany(MbwayAccounts::class, ['customer_id' => 'id']);
    }
    public function getPaypalAccounts()
    {
        return $this->hasMany(PaypalAccounts::class, ['customer_id' => 'id']);
    }
    public function getReservations()
    {
        return $this->hasMany(Reservation::class, ['customer_id' => 'id']);
    }
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    // Método signup (mova do frontend para aqui)
    public function signup($username, $email, $password)
    {
        $user = new User();
        $user->username = $username;
        $user->email    = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();

        if ($user->save()) {
            $this->user_id        = $user->id;
            $this->nome           = $username; // inicial
            $this->nif            = '';
            $this->morada         = '';
            $this->telefone       = '';
            $this->data_registro  = date('Y-m-d H:i:s');
            $this->save(false);

            // Permissões
            $auth = Yii::$app->authManager;
            $auth->assign($auth->getRole('cliente'), $user->id); // ou permissões específicas

            // Envio de email (ajuste conforme teu código)
            // $this->sendEmail($user);

            return $user;
        }

        return null;
    }
}
