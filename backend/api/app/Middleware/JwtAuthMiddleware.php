<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\ErrorMapper;
use App\Service\JwtService;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtAuthMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;
    protected RequestInterface $request;
    protected HttpResponse $response;
    protected JwtService $jwtService;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request, JwtService $jwtService)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
        $this->jwtService = $jwtService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->extractToken($request);
        
        if (!$token) {
            $errorCode = ErrorMapper::TOKEN_NOT_PROVIDED;
            $status = ErrorMapper::getHttpStatusCode($errorCode);
            $message = ErrorMapper::getDefaultMessage($errorCode);
            return $this->response
                ->withStatus($status)
                ->json([
                    'data' => [],
                    'message' => $message
                ]);
        }

        try {
            // Verificar se o token está na blacklist
            if ($this->jwtService->isTokenBlacklisted($token)) {
                $errorCode = ErrorMapper::TOKEN_VALIDATION_ERROR;
                $status = ErrorMapper::getHttpStatusCode($errorCode);
                $message = ErrorMapper::getDefaultMessage($errorCode);
                return $this->response
                    ->withStatus($status)
                    ->json([
                        'data' => [],
                        'message' => $message
                    ]);
            }

            $payload = $this->jwtService->validateToken($token);
            
            // Adicionar payload ao contexto da requisição
            Context::set('jwt_payload', $payload);
            $request = $request->withAttribute('jwt_payload', $payload);

        } catch (\RuntimeException $e) {
            $errorCode = ErrorMapper::TOKEN_VALIDATION_ERROR;
            $status = ErrorMapper::getHttpStatusCode($errorCode);
            $message = ErrorMapper::getDefaultMessage($errorCode);
            return $this->response
                ->withStatus($status)
                ->json([
                    'data' => [],
                    'message' => $message
                ]);
        }

        return $handler->handle($request);
    }

    private function extractToken(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        
        if (!empty($header) && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        $token = $request->getQueryParams()['token'] ?? null;
        if ($token) {
            return $token;
        }

        return null;
    }
}