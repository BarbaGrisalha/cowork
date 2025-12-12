<?php

namespace frontend\models;


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
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \common\models\User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * Gets query for [[CustomerCardTokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerCardTokens()
    {
        return $this->hasMany(CustomerCardTokens::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[Invoices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoices::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[MbwayAccounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMbwayAccounts()
    {
        return $this->hasMany(MbwayAccounts::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[PaypalAccounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaypalAccounts()
    {
        return $this->hasMany(PaypalAccounts::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[Reservations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReservations()
    {
        return $this->hasMany(Reservations::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

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

        if ($user->save()) {
            // CRIA O PERFIL CUSTOMER AUTOMATICAMENTE
            $customer = new \frontend\models\Customer();
            $customer->user_id = $user->id;
            $customer->nome = $this->username; // nome inicial = username
            $customer->nif = '';
            $customer->morada = '';
            $customer->telefone = '';
            $customer->data_registro = date('Y-m-d H:i:s');
            $customer->save(false); // false = pula validação (vai preencher depois)

            // Atribui permissões de cliente
            $auth = Yii::$app->authManager;
            $auth->assign($auth->getPermission('fazerReserva'), $user->id);
            $auth->assign($auth->getPermission('listarMinhasReservas'), $user->id);
            $auth->assign($auth->getPermission('verReserva'), $user->id);
            $auth->assign($auth->getPermission('atualizarReserva'), $user->id);
            $auth->assign($auth->getPermission('cancelarReserva'), $user->id);

            $this->sendEmail($user);
            return $user;
        }

        return null;
    }
}
