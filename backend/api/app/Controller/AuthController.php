<?php

declare(strict_types=1);

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Service\AuthService;
use App\Constants\ErrorMapper;
use App\Exception\Handler\BusinessException;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Operações de autenticação"
 * )
 * 
 * @OA\Schema(
 *     schema="LoginRequest",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="secret123")
 * )
 *
 * @OA\Schema(
 *     schema="LoginResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
 *         @OA\Property(property="token_type", type="string", example="Bearer"),
 *         @OA\Property(property="expires_in", type="integer", example=3600)
 *     ),
 *     @OA\Property(property="message", type="string", example="Login realizado com sucesso")
 * )
 *
 * @OA\Schema(
 *     schema="LogoutResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="logged_out", type="boolean", example=true)
 *     ),
 *     @OA\Property(property="message", type="string", example="Logout realizado com sucesso")
 * )
 *
 * @Controller(prefix="/auth")
 */
class AuthController
{
    public function __construct(
        private AuthService $authService,
        private ResponseInterface $response,
        private RequestInterface $request
    ) {}

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Realiza login e obtém token JWT",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Credenciais inválidas"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="integer", example=401),
     *                 @OA\Property(property="message", type="string", example="Email ou senha incorretos")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados de entrada inválidos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dados de entrada inválidos"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="integer", example=422),
     *                 @OA\Property(property="details", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="field", type="string", example="email"),
     *                         @OA\Property(property="message", type="string", example="O campo email é obrigatório")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor"),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro")
     *         )
     *     )
     * )
     */
    public function login(): Response
    {
        $data = $this->request->all();
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        try {
            $loginData = $this->authService->login($email, $password);
            return $this->response->json([
                'data' => $loginData,
                'message' => 'Login realizado com sucesso'
            ]);
        } catch (BusinessException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'data' => [],
                    'message' => $e->getMessage(),
                    'error' => $e->getMessage()
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
     *     path="/auth/logout",
     *     summary="Realiza logout e invalida o token JWT",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/LogoutResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token não fornecido ou inválido",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token não fornecido"),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="integer", example=401),
     *                 @OA\Property(property="message", type="string", example="Token de autenticação necessário")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor"),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro")
     *         )
     *     )
     * )
     */
    public function logout(): Response
    {
        try {
            $token = $this->request->getHeaderLine('Authorization');
            $this->authService->logout($token);
            return $this->response->json([
                'data' => [
                    'logged_out' => true
                ],
                'message' => 'Logout realizado com sucesso'
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