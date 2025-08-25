<?php

namespace App\Service;

use App\Constants\ErrorMapper;
use App\Exception\Handler\BusinessException;
use App\Service\JwtService;
use Hyperf\Utils\ApplicationContext;
use App\Model\Account;
use function Hyperf\Support\env;

class AuthService
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function login(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            $errorCode = ErrorMapper::LOGIN_VALIDATION_ERROR;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['field' => empty($email) ? 'email' : 'password'],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        if (!$this->validateCredentials($email, $password)) {
            $errorCode = ErrorMapper::LOGIN_UNAUTHORIZED;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['field' => 'email'],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        // Gera o token com base no dono e no claim
        $token = $this->jwtService->generateToken([
            'sub' => $email,
            'email' => $email,
        ]);

        // Pega o tempo de expiração e calcula datas
        $expiresIn = (int) env('JWT_EXPIRE', 3600); 
        $createdAt = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'created_at' => $createdAt,
            'expires_at' => $expiresAt
        ];
    }

    public function logout(?string $token): void
    {
        if (empty($token)) {
            $errorCode = ErrorMapper::TOKEN_VALIDATION_ERROR;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['field' => 'token'],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }
        $token = str_replace('Bearer ', '', $token);
        if ($token) {
            $this->jwtService->invalidateToken($token);
        }
    }

    private function validateCredentials(string $email, string $password): bool
    {
        $envEmail = env('AUTH_EMAIL', 'user@example.com');
        $envPassword = env('AUTH_PASSWORD', 'secret123');

        if (empty($envEmail) || empty($envPassword)) {
            return false;
        }

        // TODO: validar no banco
        return $email === $envEmail && $password === $envPassword;
    }
}
