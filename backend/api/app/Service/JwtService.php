<?php

declare(strict_types=1);

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis;
use RuntimeException;
use function Hyperf\Support\env;

class JwtService
{
    private string $secret;
    private string $algorithm;

    public function __construct()
    {
        $this->secret = env('JWT_SECRET', 'default_secret');
        $this->algorithm = env('JWT_ALGORITHM', 'HS256');

        if (empty($this->secret)) {
            throw new RuntimeException('JWT_SECRET não configurado');
        }
    }

    public function generateToken(array $payload, int $expire = null): string
    {
        $issuedAt = time();
        $expire = $expire ?? (int) env('JWT_EXPIRE', 3600);
        
        $tokenPayload = array_merge([
            'iat' => $issuedAt,
            'exp' => $issuedAt + $expire,
            'jti' => uniqid('', true),
        ], $payload);

        return JWT::encode($tokenPayload, $this->secret, $this->algorithm);
    }

    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            throw new RuntimeException('Token inválido: ' . $e->getMessage());
        }
    }

    public function invalidateToken(string $token): void
    {
        $decoded = $this->validateToken($token);
        $exp = $decoded['exp'] ?? null;
        
        if ($exp) {
            $redis = ApplicationContext::getContainer()->get(Redis::class);
            $key = "jwt:blacklist:" . $token;
            $ttl = $exp - time();
            
            if ($ttl > 0) {
                $redis->setex($key, $ttl, '1');
            }
        }
    }

    public function isTokenBlacklisted(string $token): bool
    {
        $redis = ApplicationContext::getContainer()->get(Redis::class);
        $key = "jwt:blacklist:" . $token;
        return (bool) $redis->exists($key);
    }
}