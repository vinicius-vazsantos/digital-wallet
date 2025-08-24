<?php

namespace App\Controller;

use App\Service\EmailService;
use App\Constants\ErrorMapper;
use App\Exception\Handler\BusinessException;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Hyperf\Context\ApplicationContext;

/**
 * @AutoController(prefix="test-email")
 */
class TestEmailController
{
    protected RequestInterface $request;
    protected ResponseInterface $response;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @OA\Post(
     *     path="/accounts/test-email",
     *     summary="Envia um e-mail de teste de saque",
     *     tags={"TestEmail"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="to", type="string", example="no-reply@example.com"),
     *             @OA\Property(property="amount", type="number", example=100.00),
     *             @OA\Property(property="pixKey", type="string", example="chave-pix-exemplo"),
     *             @OA\Property(property="pixType", type="string", example="email"),
     *             @OA\Property(property="date", type="string", example="24/08/2025 10:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="E-mail enviado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="E-mail enviado com sucesso!")
     *         )
     *     )
     * )
     */
    public function send(): Response
    {
        try {
            $data = $this->request->all();
            $to = $data['to'] ?? 'no-reply@example.com';
            $amount = isset($data['amount']) ? (float)$data['amount'] : 100.00;
            $pixKey = $data['pixKey'] ?? 'chave-pix-exemplo';
            $pixType = $data['pixType'] ?? 'email';
            $date = $data['date'] ?? date('d/m/Y H:i');

            $emailService = ApplicationContext::getContainer()->get(EmailService::class);
            $emailService->sendWithdrawalEmail($to, $amount, $pixKey, $pixType, $date);

            return $this->response->json([
                'data' => [
                    'to' => $to,
                    'amount' => $amount,
                    'pixKey' => $pixKey,
                    'pixType' => $pixType,
                    'date' => $date
                ],
                'message' => 'E-mail enviado com sucesso!'
            ]);
        } catch (BusinessException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'data' => [],
                    'message' => $e->getMessage(),
                    'error' => $e->toArray()
                ]);

        } catch (Exception $e) {
            $errorCode = ErrorMapper::INTERNAL_ERROR;
            $status = ErrorMapper::getHttpStatusCode($errorCode);
            $message = ErrorMapper::getDefaultMessage($errorCode);
            return $this->response
                ->withStatus($status)
                ->json([
                    'data' => [],
                    'message' => $message
                ]);
        }
    }
}
