<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Reservation;
use common\models\Customer;
use common\models\Rooms;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class InvoiceController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);

        if (!$customer) {
            throw new NotFoundHttpException('Perfil não encontrado.');
        }

        $reservasPagas = Reservation::find()
            ->joinWith(['room', 'payments'])
            ->where(['reservations.customer_id' => $customer->id])
            ->andWhere(['payments.status' => 'aprovado'])
            ->orderBy('hora_inicio_agendada DESC')
            ->all();

        return $this->render('index', [
            'reservasPagas' => $reservasPagas,
        ]);
    }

    public function actionView($id)
    {
        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        if (!$customer) {
            throw new NotFoundHttpException('Perfil não encontrado.');
        }

        $reserva = Reservation::find()
            ->joinWith(['room', 'customer', 'payments'])
            ->where([
                'reservations.id' => $id,
                'reservations.customer_id' => $customer->id
            ])
            ->one();

        if (!$reserva || !$reserva->hasPaidPayment()) {
            throw new NotFoundHttpException('Fatura não encontrada ou não paga.');
        }

        return $this->render('view', ['reserva' => $reserva]);
    }

    public function actionPdf($id)
    {
        $customer = Customer::findOne(['user_id' => Yii::$app->user->id]);
        if (!$customer) {
            throw new NotFoundHttpException('Perfil não encontrado.');
        }

        $reserva = Reservation::find()
            ->joinWith(['room', 'customer', 'payments'])
            ->where([
                'reservations.id' => $id,
                'reservations.customer_id' => $customer->id
            ])
            ->one();

        if (!$reserva || !$reserva->hasPaidPayment()) {
            throw new NotFoundHttpException('Fatura não encontrada ou não paga.');
        }

        $content = $this->renderPartial('_pdf', ['reserva' => $reserva]);

        $pdf = new \kartik\mpdf\Pdf([
            'mode' => \kartik\mpdf\Pdf::MODE_UTF8,
            'format' => \kartik\mpdf\Pdf::FORMAT_A4,
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
            'content' => $content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            'options' => ['title' => 'Fatura - Cowork IPLeiria'],
            'methods' => [
                'SetHeader' => ['Fatura Nº ' . $reserva->reservation_code],
                'SetFooter' => ['Página {PAGENO}'],
            ]
        ]);

        return $pdf->render();
    }
}
