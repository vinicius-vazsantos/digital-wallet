<?php

namespace App\Controller;

use App\Service\AccountWithdrawService;
use App\Exception\Handler\BusinessException;
use App\Constants\ErrorMapper;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Annotation\Validated;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * @OA\Tag(
 *     name="AccountWithdraw",
 *     description="Operações de saque de conta"
 * )
 *
 * @OA\Schema(
 *     schema="Withdraw",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="withdraw-uuid-1234"),
 *     @OA\Property(property="account_id", type="string", example="account-uuid-1234"),
 *     @OA\Property(property="amount", type="number", format="float", example=50.00),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="status", type="string", example="pending")
 * )
 *
 * @OA\Schema(
 *     schema="WithdrawCreate",
 *     required={"method", "pix", "amount", "schedule"},
 *     @OA\Property(property="method", type="string", example="PIX"),
 *     @OA\Property(
 *         property="pix",
 *         type="object",
 *         required={"type", "key"},
 *         @OA\Property(property="type", type="string", example="email"),
 *         @OA\Property(property="key", type="string", example="no-reply@example.com")
 *     ),
 *     @OA\Property(property="amount", type="number", example=10000),
 *     @OA\Property(property="schedule", type="string", example="2025-08-24 00:00")
 * )
 *
 * @Controller(prefix="accounts/{accountId}/balance")
 */
class AccountWithdrawController
{
    protected RequestInterface $request;
    protected ResponseInterface $response;

    public function __construct(RequestInterface $request, ResponseInterface $response, private AccountWithdrawService $service)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @OA\Get(
     *     path="/accounts/{accountId}/balance/withdraws",
     *     summary="Lista todos os saques da conta",
     *     tags={"AccountWithdraw"},
     *     @OA\Parameter(
     *         name="accountId",
     *         in="path",
     *         required=true,
     *         description="UUID da conta",
     *         @OA\Schema(type="string", example="account-uuid-1234")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Número da página",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Itens por página",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de saques",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Withdraw"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function getAll(?string $accountId): Response
    {
        try {
            // Captura parâmetros de paginação e busca
            $page = (int) $this->request->input('page', 1);
            $limit = (int) $this->request->input('limit', 10);
            $withdraws = $this->service->listWithdraws($accountId, $page, $limit);
            return $this->response->json([
                'data' => $withdraws
            ]);

        } catch (BusinessException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'data' => [],
                    'error' => $e->toArray()
                ]);

        } catch (\Throwable $e) {
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

    /**
     * @OA\Get(
     *     path="/accounts/{accountId}/balance/withdraws/{withdrawId}",
     *     summary="Obtém um saque pelo UUID da conta e do saque",
     *     tags={"AccountWithdraw"},
     *     @OA\Parameter(
     *         name="accountId",
     *         in="path",
     *         required=true,
     *         description="UUID da conta",
     *         @OA\Schema(type="string", example="account-uuid-1234")
     *     ),
     *     @OA\Parameter(
     *         name="withdrawId",
     *         in="path",
     *         required=true,
     *         description="UUID do saque",
     *         @OA\Schema(type="string", example="withdraw-uuid-1234")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Saque encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Withdraw")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Saque não encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function getById(?string $accountId, ?string $withdrawId): Response
    {
        try {
            $withdraw = $this->service->getWithdraw($accountId, $withdrawId);

            return $this->response->json([
                'data' => $withdraw
            ]);
        } catch (BusinessException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'data' => [],
                    'error' => $e->toArray()
                ]);

        } catch (\Throwable $e) {
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

    /**
     * @OA\Post(
     *     path="/accounts/{accountId}/balance/withdraws",
     *     summary="Cria um novo saque para a conta",
     *     tags={"AccountWithdraw"},
     *     @OA\Parameter(
     *         name="accountId",
     *         in="path",
     *         required=true,
     *         description="UUID da conta",
     *         @OA\Schema(type="string", example="account-uuid-1234")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/WithdrawCreate")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Saque criado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Withdraw")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function create(string $accountId): Response
    {
        try {
            $data = $this->request->all();
            $data['account_id'] = $accountId;

            $withdraw = $this->service->createWithdraw($data);

            return $this->response->json([
                'data' => $withdraw
            ]);
            
        } catch (BusinessException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'data' => [],
                    'error' => $e->toArray()
                ]);
            
        } catch (\Throwable $e) {
            $errorCode = ErrorMapper::INTERNAL_ERROR;
            $status = ErrorMapper::getHttpStatusCode($errorCode);
            $message = ErrorMapper::getDefaultMessage($errorCode);
            return $this->response
                ->withStatus($status)
                ->json([
                    'data' => [],
                    'message' => $message,
                    'original_message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
        }
    }
}