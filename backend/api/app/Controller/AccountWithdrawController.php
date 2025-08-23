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
 * @Controller(prefix="account/{accountId}/balance")
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

    // Lista todos os saques da conta
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

    // Obtém o saque pelo UUID do saque e da conta
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

    // Cria um novo saque
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