<?php

namespace App\Service;

use App\Model\Account;
use App\Model\AccountWithdraw;
use App\Model\AccountWithdrawPix;
use App\Exception\Handler\BusinessException;
use App\Constants\ErrorMapper;
use Hyperf\DbConnection\Db;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class AccountWithdrawService
{
    // Lista todos os saques da conta
    public function listWithdraws(?string $accountId, int $page = 1, int $limit = 10, ?string $search = null, ?string $createdAt = null): array
    {
        if (empty($accountId)) {
            $errorCode = ErrorMapper::REQUIRED_FIELD_MISSING;
            throw new BusinessException(
                $errorCode, 
                ErrorMapper::getDefaultMessage($errorCode),
                ['field' => 'accountId'],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        $query = AccountWithdraw::query()->where('account_id', $accountId);

        if ($search) {
            $query->where('description', 'like', "%{$search}%");
        }

        if ($createdAt) {
            $query->whereDate('created_at', $createdAt);
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

    // Buscar saques por UUID
    public function getWithdraw(?string $accountId, ?string $withdrawId): ?AccountWithdraw
    {
        if (empty($accountId) || empty($withdrawId)) {
            $errorCode = ErrorMapper::REQUIRED_FIELD_MISSING;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['field' => empty($accountId) ? 'accountId' : 'withdrawId'],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        $withdraw = AccountWithdraw::where('id', $withdrawId)
            ->where('account_id', $accountId)
            ->first();

        if (!$withdraw) {
            $errorCode = ErrorMapper::WITHDRAW_NOT_FOUND;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['withdraw_id' => $withdrawId, 'account_id' => $accountId],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        return $withdraw;
    }

    // Cria um novo saque
    public function createWithdraw(array $data)
    {
        // Valida dados obrigatórios
        $this->validateRequiredFields($data);
        
        $account = Account::find($data['account_id']);
        if (!$account) {
            $errorCode = ErrorMapper::ACCOUNT_NOT_FOUND;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['account_id' => $data['account_id']],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        $amount = (float)$data['amount'];
        $this->validateAmount($amount);

        // Valida tipo PIX
        $this->validatePixType($data['pix']['type']);

        // Processa agendamento
        $scheduled = isset($data['schedule']) && !empty($data['schedule']);
        $scheduledFor = $scheduled ? Carbon::parse($data['schedule']) : null;

        if ($scheduled) {
            $this->validateScheduling($scheduledFor);
        } else {
            $this->validateBalance($account, $amount);
        }

        return Db::transaction(function () use ($account, $amount, $data, $scheduled, $scheduledFor) {
            try {
                $withdraw = new AccountWithdraw();
                $withdraw->id = Uuid::uuid4()->toString();
                $withdraw->account_id = $account->id;
                $withdraw->amount = $amount;
                $withdraw->method = strtolower($data['method']);
                $withdraw->scheduled = $scheduled;
                $withdraw->scheduled_for = $scheduledFor;
                $withdraw->done = false;
                $withdraw->error = false;
                $withdraw->save();

                // Cria detalhes PIX
                $pix = new AccountWithdrawPix();
                $pix->account_withdraw_id = $withdraw->id;
                $pix->type = $data['pix']['type'];
                $pix->key = $data['pix']['key'];
                $pix->save();

                if (!$scheduled) {
                    // Saque imediato: deduz saldo e processa
                    $this->processImmediateWithdraw($account, $withdraw, $amount);
                }

                return $this->formatWithdrawResponse($withdraw);

            } catch (\Exception $e) {
                $errorCode = ErrorMapper::DATABASE_ERROR;
                throw new BusinessException(
                    $errorCode,
                    ErrorMapper::getDefaultMessage($errorCode),
                    ['original_message' => $e->getMessage()],
                    ErrorMapper::getHttpStatusCode($errorCode)
                );
            }
        });
    }

    // Valida tipo PIX
    private function validatePixType(string $pixType): void
    {
        $validTypes = ['email', 'cpf_cnpj', 'phone', 'random_key'];
        
        if (!in_array($pixType, $validTypes)) {
            $errorCode = ErrorMapper::INVALID_PIX_TYPE;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                [
                    'valid_types' => $validTypes,
                    'provided_type' => $pixType
                ],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }
    }

    // Processa saque imediato
    private function processImmediateWithdraw(Account $account, AccountWithdraw $withdraw, float $amount): void
    {
        // Verifica saldo novamente (double-check)
        if ($account->balance < $amount) {
            $errorCode = ErrorMapper::INSUFFICIENT_BALANCE;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                [
                    'current_balance' => $account->balance,
                    'requested_amount' => $amount,
                    'deficit' => $amount - $account->balance
                ],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        // Deduz saldo
        $account->balance -= $amount;
        $account->save();

        // TODO: Envia email assíncrono
        
        // Marca como processado
        $withdraw->done = true;
        $withdraw->processed_at = Carbon::now();
        $withdraw->save();
    }

    // Valida campos obrigatórios
    private function validateRequiredFields(array $data): void
    {
        $requiredFields = [
            'account_id', 'method', 'amount', 
            'pix.type', 'pix.key'
        ];

        $missingFields = [];

        foreach ($requiredFields as $field) {
            $keys = explode('.', $field);
            $value = $data;

            foreach ($keys as $key) {
                if (!isset($value[$key]) || $value[$key] === '') {
                    $missingFields[] = $field;
                    break;
                }
                $value = $value[$key];
            }
        }

        if (!empty($missingFields)) {
            $errorCode = ErrorMapper::REQUIRED_FIELD_MISSING;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['missing_fields' => $missingFields],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        // Validação específica para método PIX
        if ($data['method'] !== 'PIX') {
            $errorCode = ErrorMapper::UNSUPPORTED_WITHDRAW_METHOD;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['supported_methods' => ['PIX']],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }
    }

    // Valida valor do saque
    private function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            $errorCode = ErrorMapper::INVALID_WITHDRAW_AMOUNT;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['amount' => $amount],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }
    }

    // Valida agendamento
    private function validateScheduling(?Carbon $scheduledFor): void
    {
        if (!$scheduledFor) {
            $errorCode = ErrorMapper::SCHEDULING_ERROR;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                [],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        if ($scheduledFor->isPast()) {
            $errorCode = ErrorMapper::PAST_SCHEDULING_NOT_ALLOWED;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['scheduled_for' => $scheduledFor->toISOString()],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }

        if ($scheduledFor->diffInDays(Carbon::now()) > 7) {
            $errorCode = ErrorMapper::SCHEDULING_LIMIT_EXCEEDED;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                ['max_days' => 7],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }
    }

    // Valida saldo
    private function validateBalance(Account $account, float $amount): void
    {
        if ($account->balance < $amount) {
            $errorCode = ErrorMapper::INSUFFICIENT_BALANCE;
            throw new BusinessException(
                $errorCode,
                ErrorMapper::getDefaultMessage($errorCode),
                [
                    'current_balance' => $account->balance,
                    'requested_amount' => $amount,
                    'deficit' => $amount - $account->balance
                ],
                ErrorMapper::getHttpStatusCode($errorCode)
            );
        }
    }

    // Formata a resposta do saque
    private function formatWithdrawResponse(AccountWithdraw $withdraw): array
    {
        return [
            'id' => $withdraw->id,
            'account_id' => $withdraw->account_id,
            'amount' => $withdraw->amount,
            'method' => $withdraw->method,
            'scheduled' => $withdraw->scheduled,
            'scheduled_for' => $withdraw->scheduled_for?->toISOString(),
            'done' => $withdraw->done,
            'error' => $withdraw->error,
            'failure_reason' => $withdraw->failure_reason,
            'processed_at' => $withdraw->processed_at?->toISOString(),
            'created_at' => $withdraw->created_at->toISOString(),
            'pix' => [
                'type' => $withdraw->pixDetails->type,
                'key' => $withdraw->pixDetails->key
            ]
        ];
    }

    // Processa saque agendado
    public function processScheduledWithdraw(AccountWithdraw $withdraw)
    {
        return Db::transaction(function () use ($withdraw) {
            $account = Account::find($withdraw->account_id);
            
            if (!$account) {
                $withdraw->markAsFailed('Conta não encontrada');
                return;
            }

            if ($account->balance < $withdraw->amount) {
                $withdraw->markAsFailed('Saldo insuficiente no momento do agendamento');
                return;
            }

            // Deduz saldo
            $account->balance -= $withdraw->amount;
            $account->save();

            // TODO: Envia email
            
            // Marca como processado
            $withdraw->markAsProcessed();
        });
    }
}