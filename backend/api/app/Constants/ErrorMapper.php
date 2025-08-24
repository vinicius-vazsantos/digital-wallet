<?php

declare(strict_types=1);

namespace App\Constants;

class ErrorMapper
{
    // Definição das constantes individuais
    public const VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const REQUIRED_FIELD_MISSING = 'REQUIRED_FIELD_MISSING';
    public const INVALID_DATA_TYPE = 'INVALID_DATA_TYPE';
    public const ACCOUNT_NOT_FOUND = 'ACCOUNT_NOT_FOUND';
    public const WITHDRAW_NOT_FOUND = 'WITHDRAW_NOT_FOUND';
    public const INSUFFICIENT_BALANCE = 'INSUFFICIENT_BALANCE';
    public const INVALID_WITHDRAW_AMOUNT = 'INVALID_WITHDRAW_AMOUNT';
    public const INVALID_BALANCE = 'INVALID_BALANCE';
    public const SCHEDULING_ERROR = 'SCHEDULING_ERROR';
    public const PAST_SCHEDULING_NOT_ALLOWED = 'PAST_SCHEDULING_NOT_ALLOWED';
    public const SCHEDULING_LIMIT_EXCEEDED = 'SCHEDULING_LIMIT_EXCEEDED';
    public const INVALID_PIX_KEY = 'INVALID_PIX_KEY';
    public const INVALID_PIX_TYPE = 'INVALID_PIX_TYPE';
    public const UNSUPPORTED_WITHDRAW_METHOD = 'UNSUPPORTED_WITHDRAW_METHOD';
    public const INTERNAL_ERROR = 'INTERNAL_ERROR';
    public const DATABASE_ERROR = 'DATABASE_ERROR';
    public const TRANSACTION_ERROR = 'TRANSACTION_ERROR';
    public const NO_FIELDS_TO_UPDATE = 'NO_FIELDS_TO_UPDATE';
    public const EMAIL_SEND_FAILED = 'EMAIL_SEND_FAILED';
    public const LOGIN_VALIDATION_ERROR = 'LOGIN_VALIDATION_ERROR';
    public const LOGIN_UNAUTHORIZED = 'LOGIN_UNAUTHORIZED';
    public const TOKEN_VALIDATION_ERROR = 'TOKEN_VALIDATION_ERROR';
    public const TOKEN_NOT_PROVIDED = 'TOKEN_NOT_PROVIDED';
    public const EMAIL_SENDING_FAILED = 'EMAIL_SENDING_FAILED';
    public const POSSIBLE_INSUFFICIENT_BALANCE = 'POSSIBLE_INSUFFICIENT_BALANCE';

    /**
     * Mapeamento de códigos de erro para códigos HTTP
     */
    public const HTTP_STATUS_MAP = [
        self::POSSIBLE_INSUFFICIENT_BALANCE => 200,

        // Validação - 400 Bad Request
        self::VALIDATION_ERROR => 400,
        self::REQUIRED_FIELD_MISSING => 400,
        self::INVALID_DATA_TYPE => 400,
        self::INVALID_WITHDRAW_AMOUNT => 400,
        self::INVALID_BALANCE => 400,
        self::SCHEDULING_ERROR => 400,
        self::PAST_SCHEDULING_NOT_ALLOWED => 400,
        self::SCHEDULING_LIMIT_EXCEEDED => 400,
        self::INVALID_PIX_KEY => 400,
        self::INVALID_PIX_TYPE => 400,
        self::UNSUPPORTED_WITHDRAW_METHOD => 400,
        self::NO_FIELDS_TO_UPDATE => 400,
        self::LOGIN_VALIDATION_ERROR => 422,
        self::LOGIN_UNAUTHORIZED => 401,
        self::TOKEN_VALIDATION_ERROR => 401,
        self::TOKEN_NOT_PROVIDED => 401,

        // Não encontrado - 404 Not Found
        self::ACCOUNT_NOT_FOUND => 404,
        self::WITHDRAW_NOT_FOUND => 404,

        // Conflito - 409 Conflict
        self::INSUFFICIENT_BALANCE => 409,
        
        // Erro interno - 500 Internal Server Error
        self::INTERNAL_ERROR => 500,
        self::DATABASE_ERROR => 500,
        self::TRANSACTION_ERROR => 500,
        self::EMAIL_SEND_FAILED => 500,
        self::EMAIL_SENDING_FAILED => 500,
    ];

    /**
     * Mensagens padrão para cada código de erro
     */
    public const DEFAULT_MESSAGES = [
        self::POSSIBLE_INSUFFICIENT_BALANCE => 'Atenção: o saldo atual pode não ser suficiente quando o agendamento for processado.',
        self::VALIDATION_ERROR => 'Erro de validação',
        self::REQUIRED_FIELD_MISSING => 'Campos obrigatórios não fornecidos',
        self::INVALID_DATA_TYPE => 'Tipo de dado inválido',
        self::ACCOUNT_NOT_FOUND => 'Conta não encontrada',
        self::WITHDRAW_NOT_FOUND => 'Saque não encontrado',
        self::INSUFFICIENT_BALANCE => 'Saldo insuficiente',
        self::INVALID_WITHDRAW_AMOUNT => 'Valor de saque inválido',
        self::INVALID_BALANCE => 'Valor de saldo inválido',
        self::SCHEDULING_ERROR => 'Erro no agendamento',
        self::PAST_SCHEDULING_NOT_ALLOWED => 'Não é permitido agendar para o passado',
        self::SCHEDULING_LIMIT_EXCEEDED => 'Limite de agendamento excedido',
        self::INVALID_PIX_KEY => 'Chave PIX inválida',
        self::INVALID_PIX_TYPE => 'Tipo de PIX inválido',
        self::UNSUPPORTED_WITHDRAW_METHOD => 'Método de saque não suportado',
        self::INTERNAL_ERROR => 'Erro interno do servidor',
        self::DATABASE_ERROR => 'Erro de banco de dados',
        self::TRANSACTION_ERROR => 'Erro na transação',
        self::NO_FIELDS_TO_UPDATE => 'Nenhum campo válido foi fornecido para atualização',
        self::EMAIL_SEND_FAILED => 'Falha ao enviar e-mail',
        self::LOGIN_VALIDATION_ERROR => 'Email e senha são obrigatórios',
        self::LOGIN_UNAUTHORIZED => 'Email ou senha incorretos',
        self::TOKEN_VALIDATION_ERROR => 'Token inválido',
        self::TOKEN_NOT_PROVIDED => 'Token não fornecido',
        self::EMAIL_SENDING_FAILED => 'Falha ao enviar e-mail',
    ];

    /**
     * Obtém o código HTTP para um código de erro
     */
    public static function getHttpStatusCode(string $errorCode): int
    {
        return self::HTTP_STATUS_MAP[$errorCode] ?? 400;
    }

    /**
     * Obtém a mensagem padrão para um código de erro
     */
    public static function getDefaultMessage(string $errorCode): string
    {
        return self::DEFAULT_MESSAGES[$errorCode] ?? 'Erro desconhecido';
    }
}