<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
// 1. SERVICES E REPOSITÓRIOS (Injetados no construtor)
use App\Payment\Service\PaymentCreationService;
use App\Reservation\Repository\ReservationRepository;

// 2. DTO (Data Transfer Object)
use App\Payment\DTO\PaymentRequestData;

// 3. MODELO ATIVO (Para constantes de status)
// Usamos o nome do Model (no singular ou plural, dependendo da sua escolha final)
// Vou assumir que você refatorou para o singular (Reservation) como boa prática:
use common\models\Reservation; // Se você usou o plural, use 'Reservations'

// 4. EXCEÇÃO DE NEGÓCIO (Para o bloco catch)
use App\Payment\Exception\PaymentGatewayException;



class CheckoutController extends \yii\web\Controller
{
    /** @var PaymentCreationService */
    private $paymentService;

    /** @var ReservationRepository */
    private $reservationRepository;

    // Configuração de injeção via construtor (requer configuração no container de DI do Yii)
    // Se não estiver usando o container, você pode instanciar aqui, mas não é a prática sênior!
    public function __construct($id, $module, PaymentCreationService $paymentService, ReservationRepository $reservationRepository, $config = [])
    {
        $this->paymentService = $paymentService;
        $this->reservationRepository = $reservationRepository;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        /*
        // 1. Configura a negociação de conteúdo para JSON
        $behaviors['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator', // AGORA É UMA STRING
            'formats' => [
                // Response está no use, então pode continuar ou usar 'yii\web\Response'
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        */

        // 2. Cria a exceção para o CSRF
        $behaviors['csrfFilter'] = [
            // CORREÇÃO DA LINHA 59: Usando o nome da classe como string literal
            'class' => 'yii\filters\CsrfFilter', // AGORA É UMA STRING
            'except' => ['pay'],
        ];
        /*
        // 3. Garante que a action 'pay' só aceite POST
        $behaviors['verbs'] = [
            'class' => 'yii\filters\VerbFilter', // AGORA É UMA STRING
            'actions' => [
                'pay' => ['POST'],
            ],
        ];
*/
        return $behaviors;
    }
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {

        // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
    /**
     * Ação RESTful para processar o pagamento de uma Reserva (Order).
     * Rota: POST /checkout/pay?id=123
     * * @param int $id O ID da Reservation (pedido) no status 'rascunho'.
     * @return array Resposta JSON.
     */
    public function actionPay(int $id): array
    {
        // 1. Coleta e Valida os Dados (do JSON Body)
        $data = Yii::$app->request->getBodyParams();

        try {
            // Usa o DTO para garantir que o token e o método existem e são válidos.
            $paymentData = new PaymentRequestData($data['paymentToken'] ?? null, $data['method'] ?? null);
        } catch (\InvalidArgumentException $e) {
            Yii::$app->response->statusCode = 400; // Bad Request
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        // 2. Localiza a Reserva e verifica Status
        $reservation = $this->reservationRepository->find($id);

        if (!$reservation || $reservation->status !== Reservation::STATUS_DRAFT) { //linha 75
            Yii::$app->response->statusCode = 404; // Not Found
            return ['status' => 'error', 'message' => 'Reserva não encontrada ou já paga.'];
        }

        // **3. Execução da Lógica Transacional (Service Layer)**
        try {
            // O Service faz a magia: Pagamento, Atualização do DB, e COMMIT.
            $completedReservation = $this->paymentService->executePayment(
                $reservation->id,
                $paymentData->getPaymentToken(),
                $paymentData->getMethod()
            );

            // 4. Pós-Pagamento (Geração de QR Code/Access Code - Simulação)
            // Aqui você chamaria o AccessCodeService para gerar o código e o QR.
            // Ex: $accessCode = $this->accessCodeService->generate($completedReservation);

            // 5. Resposta de Sucesso
            Yii::$app->response->statusCode = 200; // OK
            return [
                'status' => 'success',
                'order_id' => $completedReservation->id,
                'total_pago' => $completedReservation->total_estimado,
                'message' => 'Compra efetuada com sucesso!',
                'qr_code_data' => 'DATA_PARA_GERACAO_DO_QR_CODE_DA_RESERVA_' . $completedReservation->id,
            ];
        } catch (\App\Payment\Exception\PaymentGatewayException $e) {
            // Erro vindo da API externa (cartão negado, etc.)
            Yii::$app->response->statusCode = 400; // Client Error
            Yii::error("Pagamento falhou para Reserva #{$id}: " . $e->getMessage());
            return ['status' => 'failure', 'message' => 'Falha no processamento: ' . $e->getMessage()];
        } catch (\Exception $e) {
            // Erro interno (DB, Rollback, Lógica de Negócio)
            Yii::$app->response->statusCode = 500; // Internal Server Error
            Yii::error("Erro fatal ao processar checkout para Reserva #{$id}: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Erro interno do servidor. Tente novamente.'];
        }
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Action que recebe o token do pagamento e processa a transação.
     * Esta é a Action 'pagar'.
     */
    public function actionPagar()
    {
        // 1. Recebe o token, valor e dados do pedido.
        $request = Yii::$app->request;

        // Pelo seu código, o token viria via POST.
        $token = $request->post('payment_token');

        // Assumindo que você tem um método auxiliar para carregar o pedido/reserva
        $pedido = $this->findPedido(Yii::$app->user->id);

        // Se o pedido não existir ou já estiver pago, pare aqui!
        if (!$pedido) {
            // Lógica de redirecionamento ou throw 404
        }

        try {
            // 2. Chama o Service/Componente (que você configurou em common\components ou Services)
            // Lembre-se, o componente `paymentGatewayService` deve estar configurado no seu `common/config/main.php`
            $gateway = Yii::$app->paymentGatewayService;

            // O componente faz a chamada real para a API do Gateway
            $response = $gateway->processPayment($token, $pedido->valor);

            // 3. O Retorno do Gateway
            if ($response->isApproved()) {
                $pedido->status = 'APROVADO';
            } elseif ($response->isPending()) {
                // Pendente (aguardando confirmação do Gateway)
                $pedido->status = 'AGUARDANDO_GATEWAY';
            } else {
                $pedido->status = 'NEGADO';
            }

            // 4. Salva a Transaction ID (essencial para Webhooks e rastreio)
            $pedido->transaction_id = $response->getTransactionId();

            // 5. Use TRANSAÇÕES DE BANCO (SQL/MySQL)
            // Se falhar a gravação do status, reverte. Cantiga de Ninar, lembra?
            $this->savePedidoInTransaction($pedido);

            // Redireciona para o sucesso (onde o cliente deve ir)
            return $this->redirect(['sucesso']);
        } catch (\Exception $e) {
            // Log do erro (logar é vida, Git/GitHub sabe disso)
            Yii::error("Erro no pagamento do Pedido #{$pedido->id}: " . $e->getMessage(), __METHOD__);
            $pedido->status = 'ERRO';
            $pedido->save(); // Salva o erro antes de redirecionar para 'falha'
            return $this->redirect(['falha', 'mensagem' => 'Pagamento negado ou erro interno.']);
        }
    }
}
