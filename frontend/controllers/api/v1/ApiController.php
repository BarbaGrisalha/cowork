<?php

namespace frontend\controllers\api\v1;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\RateLimitInterface;
use yii\base\RateLimit;
use common\models\Reservation;
use common\models\Rooms;
use common\models\Payment;
use common\models\User;
use common\models\Customer;
use yii\data\ActiveDataProvider;

class ApiController extends Controller implements RateLimitInterface
{
    public $enableSession = false;

    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => CompositeAuth::class,
                'authMethods' => [
                    HttpBearerAuth::class,
                ],
            ],
            'rateLimit' => [
                'class' => RateLimit::class,
                'allowance' => 100,
                'rate' => 50,
            ],
            'cors' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'OPTIONS'],
                    'Access-Control-Allow-Headers' => ['Content-Type', 'Authorization'],
                ],
            ],
        ];
    }

    public function actions()
    {
        $this->enableCsrfValidation = false;
        return parent::actions();
    }

    public function actionLogin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');

        $user = User::findByUsername($username);
        if ($user && $user->validatePassword($password)) {
            $jwt = $user->getJwt(true);
            return [
                'success' => true,
                'token' => $jwt,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                ],
                'complete_profile' => empty($user->customer->nif ?? ''),
            ];
        }

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
}
