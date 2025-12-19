<?php

namespace frontend\controllers\api\v1;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\RateLimitInterface;
//use yii\base\RateLimit;
use common\models\Reservation;
use common\models\Rooms;
use common\models\Payment;
use common\models\User;
use common\models\Customer;
use yii\data\ActiveDataProvider;

class ApiController extends Controller
{
    public $enableSession = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Configuração do CORS para permitir o Insomnia/Frontend
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];

        // Autenticação: Protege tudo, EXCETO o login
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['login'], // Essencial para conseguir logar!
        ];

        return $behaviors;
    }

    public function actions()
    {
        $this->enableCsrfValidation = false;
        return parent::actions();
    }

    // O método actionLogin permanece quase igual, mas certifique-se do seguinte:
    public function actionLogin()
    {
        // O Yii rest\Controller já define o formato como JSON automaticamente

        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');

        $user = User::findByUsername($username);

        if ($user && $user->validatePassword($password)) {
            // Se você não tiver JWT configurado, use o auth_key para testar primeiro
            return [
                'success' => true,
                'token' => $user->auth_key,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                ],
            ];
        }

        Yii::$app->response->statusCode = 401;
        return ['success' => false, 'message' => 'Credenciais inválidas.'];
    }


    public function actionCompleteProfile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        if (!$customer) {
            $customer = new Customer();
            $customer->user_id = Yii::$app->user->id;
        }

        if ($customer->load(Yii::$app->request->post()) && $customer->save()) {
            return ['success' => true, 'message' => 'Perfil completo!'];
        }

        return ['success' => false, 'errors' => $customer->errors];
    }

    public function actionRooms()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $rooms = Rooms::find()
            ->where(['status' => 'ativa'])
            ->asArray()
            ->all();

        return ['success' => true, 'rooms' => $rooms];
    }

    public function actionCreateReservation()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $reservation = new Reservation();
        $reservation->load(Yii::$app->request->post(), '');
        $reservation->customer_id = Customer::findOne(['user_id' => Yii::$app->user->id])->id;

        if ($reservation->save()) {
            return ['success' => true, 'reservation_id' => $reservation->id];
        }

        return ['success' => false, 'errors' => $reservation->errors];
    }

    public function actionUserReservations()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => Reservation::find()
                ->joinWith('room')
                ->where(['customer_id' => $customer->id]),
        ]);

        return ['success' => true, 'reservations' => $dataProvider->getModels()];
    }

    public function actionPayReservation($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $reservation = Reservation::findOne($id);
        if ($reservation && $reservation->customer_id == Customer::findOne(['user_id' => Yii::$app->user->id])->id) {
            // Lógica de pagamento (ex: Stripe, PayPal)
            $reservation->status = 'pago';
            $reservation->save();

            return ['success' => true, 'message' => 'Pagamento realizado!'];
        }

        return ['success' => false, 'message' => 'Reserva não encontrada.'];
    }

    public function actionInvoice($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        $reserva = Reservation::findOne([
            'id' => $id,
            'customer_id' => $customer->id,
        ]);

        if ($reserva) {
            return ['success' => true, 'invoice' => [
                'code' => $reserva->reservation_code,
                'date' => Yii::$app->formatter->asDate($reserva->hora_inicio_agendada),
                'amount' => $reserva->total_estimado,
            ]];
        }

        return ['success' => false, 'message' => 'Fatura não encontrada.'];
    }

    public function getRateLimit($request, $action)
    {
        return [100, 50]; // 100 requests por 50 segundos
    }

    public function loadUser()
    {
        $token = Yii::$app->request->headers->get('Authorization');
        if ($token) {
            $user = User::findByJwt($token);
            return $user;
        }
        return null;
    }

    // Dentro da classe ApiController

    public function loadAllowance($request, $action)
    {
        return [Yii::$app->user->identity->allowance, Yii::$app->user->identity->allowance_updated_at];
    }

    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $user = Yii::$app->user->identity;
        $user->allowance = $allowance;
        $user->allowance_updated_at = $timestamp;
        $user->save(false);
    }
}
