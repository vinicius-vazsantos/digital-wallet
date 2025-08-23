<?php

namespace App\Service;

use App\Model\Account;
use Hyperf\DbConnection\Db;
use Ramsey\Uuid\Uuid;
use App\Constants\ErrorMapper;
use App\Exception\Handler\BusinessException;

class AccountService
{
    // Lista todas as contas
    public function listAccounts(int $page = 1, int $limit = 10, ?string $search = null, ?string $createdAt = null, bool $includeDeleted = false): array
    {
        $query = Account::query();

        if ($search) {
            $query->byName($search);
        }

        if ($createdAt) {
            $query->scopeWithCreatedAt($createdAt);
        }

        if ($includeDeleted) {
            $query->withTrashed();
        }

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'items' => $paginator->items(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    // Buscar conta por UUID
    public function getAccount(string $accountId): ?Account
    {
        $account = Account::find($accountId);
        
        if (!$account) {
            $errorCode = ErrorMapper::ACCOUNT_NOT_FOUND;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['account_id' => $accountId],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }
        return $account;
    }

    // Cria nova conta
    public function createAccount(?string $name, float $initialBalance = 0): Account
    {
        if (empty($name)) {
            $errorCode = ErrorMapper::REQUIRED_FIELD_MISSING;
            throw new BusinessException(
                $errorCode, 
                ErrorMapper::getDefaultMessage($errorCode),
                ['field' => 'name'],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        $account = new Account();
        $account->id = Uuid::uuid4()->toString();
        $account->name = $name;
        $account->balance = $initialBalance;
        $account->save();

        return $account;
    }

    // Atualiza conta por UUID
    public function updateAccount(string $accountId, array $data): Account
    {
        $account = Account::find($accountId);
        
        if (!$account) {
            $errorCode = ErrorMapper::ACCOUNT_NOT_FOUND;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['account_id' => $accountId],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        // Valida e atualiza apenas campos permitidos
        if (isset($data['name'])) {
            $account->name = $data['name'];
        }

        if (isset($data['balance'])) {
            $this->validateBalance($data['balance']);
            $account->balance = (float) $data['balance'];
        }

        $account->save();

        return $account;
    }

    // Deleta conta
    public function deleteAccount(string $accountId): Account
    {
        $account = Account::find($accountId);
        
        if (!$account) {
            $errorCode = ErrorMapper::ACCOUNT_NOT_FOUND;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['account_id' => $accountId],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        $account->delete();

        return $account;
    }

    // Valida saldo
    private function validateBalance(float $balance): void
    {
        if ($balance < 0) {
            $errorCode = ErrorCodes::INVALID_WITHDRAW_AMOUNT;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['balance' => $balance],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }
    }

    // Atualiza saldo (adiciona ou subtrai)
    public function updateBalance(string $accountId, float $amount)
    {
        $account = $this->getAccount($accountId);
        if (!$account) {
            $errorCode = ErrorMapper::ACCOUNT_NOT_FOUND;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['account_id' => $accountId],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        $newBalance = $account->balance + $amount;
        if ($newBalance < 0) {
            $errorCode = ErrorMapper::INSUFFICIENT_BALANCE;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['balance' => $newBalance],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        $account->balance = $newBalance;
        $account->save();

        return $account;
    }
}
