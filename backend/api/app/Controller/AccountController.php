<?php

namespace App\Controller;

use App\Service\AccountService;
use App\Constants\ErrorMapper;
use App\Exception\Handler\BusinessException;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;

/**
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

    // Lista todas as contas
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

    // Obtém conta por UUID
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

    // Cria conta
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

    // Atualiza conta por UUID
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

    // Deleta conta por UUID
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
