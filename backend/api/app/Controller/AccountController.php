<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Service\AccountService;
use App\Constants\ErrorMapper;
use App\Exception\Handler\BusinessException;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * @OA\Info(
 *     title="Digital Wallet API",
 *     version="1.0.0",
 *     description="Documentação da API de Contas"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use o token JWT gerado no login. Exemplo: 'Bearer {token}'"
 * )
 * 
 * @OA\Tag(
 *     name="Accounts",
 *     description="Operações de contas"
 * )
 * 
 * @OA\Schema(
 *     schema="Account",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="uuid-1234"),
 *     @OA\Property(property="name", type="string", example="João da Silva"),
 *     @OA\Property(property="balance", type="number", format="float", example=100.50),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="AccountCreate",
 *     required={"name", "balance"},
 *     @OA\Property(property="name", type="string", example="João da Silva"),
 *     @OA\Property(property="balance", type="number", format="float", example=100.50)
 * )
 *
 * @OA\Schema(
 *     schema="AccountUpdate",
 *     @OA\Property(property="name", type="string", example="João da Silva"),
 *     @OA\Property(property="balance", type="number", format="float", example=150.00)
 * )
 *
 * @AutoController(prefix="accounts")
 */
class AccountController
{
    protected RequestInterface $request;
    protected ResponseInterface $response;

    public function __construct(RequestInterface $request, ResponseInterface $response, private AccountService $accountService)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @OA\Get(
     *     path="/accounts",
     *     summary="Lista todas as contas com paginação e filtros",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Número da página (default: 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Quantidade de itens por página (default: 10)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Texto para buscar por nome",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="created_at",
     *         in="query",
     *         required=false,
     *         description="Buscar por data de criação (YYYY-MM-DD)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="include_deleted",
     *         in="query",
     *         required=false,
     *         description="Incluir contas deletadas",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de contas obtida com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="total", type="integer", example=120),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Account")
     *             ),
     *             @OA\Property(property="message", type="string", example="Contas obtidas com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Falha ao obter contas"),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro")
     *         )
     *     )
     * )
     */
    public function getAll(): Response
    {
        try {
            // Captura parâmetros de paginação e busca
            $page = (int) $this->request->input('page', 1);
            $limit = (int) $this->request->input('limit', 10);
            $search = $this->request->input('search', null);
            $createdAt = $this->request->input('created_at', null);
            $includeDeleted = filter_var(
                $this->request->input('include_deleted', 'false'),
                FILTER_VALIDATE_BOOLEAN
            );

            $accounts = $this->accountService->listAccounts($page, $limit, $search, $createdAt, $includeDeleted);

            return $this->response
                ->json([
                    'success' => true,
                    'data' => $accounts['items'],
                    'total' => $accounts['total'],
                    'limit' => $accounts['per_page'],
                    'current_page' => $accounts['current_page'],
                    'last_page' => $accounts['last_page'],
                    'message' => 'Contas obtidas com sucesso'
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

    /**
     * @OA\Get(
     *     path="/accounts/{accountId}",
     *     summary="Obtém uma conta pelo UUID",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="accountId",
     *         in="path",
     *         required=true,
     *         description="UUID da conta",
     *         @OA\Schema(type="string", example="uuid-1234")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conta encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Conta não encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function getById(string $accountId): Response
    {
        try {
            $account = $this->accountService->getAccount($accountId);
            return $this->response->json([
                'data' => $account
            ]);

        } catch (BusinessException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'data' => [],
                    'message' => $e->getMessage(),
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
     *     path="/accounts",
     *     summary="Cria uma nova conta",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AccountCreate")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Conta criada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Account")
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
    public function create(): Response
    {
        $data = $this->request->all();
        $name = $data['name'] ?? null;
        $initialBalance = $data['balance'] ?? 0;

        try {
            $account = $this->accountService->createAccount($name, $initialBalance);
            return $this->response->json([
                'data' => $account
            ], 201);

        } catch (BusinessException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'data' => [],
                    'message' => $e->getMessage(),
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
     * @OA\Put(
     *     path="/accounts/{accountId}",
     *     summary="Atualiza uma conta pelo UUID",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="accountId",
     *         in="path",
     *         required=true,
     *         description="UUID da conta",
     *         @OA\Schema(type="string", example="uuid-1234")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AccountUpdate")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conta atualizada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Conta não encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function update(string $accountId): Response
    {
        $data = $this->request->all();
        $name = $data['name'] ?? null;
        $balance = $data['balance'] ?? null;

        try {
            $updatedAccount = $this->accountService->updateAccount($accountId, [
                'name' => $name,
                'balance' => $balance
            ]);
            return $this->response->json([
                'data' => $updatedAccount,
            ]);

        } catch (BusinessException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'data' => [],
                    'message' => $e->getMessage(),
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
     * @OA\Delete(
     *     path="/accounts/{accountId}",
     *     summary="Deleta uma conta pelo UUID",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="accountId",
     *         in="path",
     *         required=true,
     *         description="UUID da conta",
     *         @OA\Schema(type="string", example="uuid-1234")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conta deletada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Conta não encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function delete(string $accountId): Response
    {
        $data = $this->request->all();
        $name = $data['name'] ?? null;
        $balance = $data['balance'] ?? null;

        try {
            $deleted = $this->accountService->deleteAccount($accountId);
            return $this->response->json([
                'data' => $deleted,
            ]);

        } catch (BusinessException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'data' => [],
                    'message' => $e->getMessage(),
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
}
