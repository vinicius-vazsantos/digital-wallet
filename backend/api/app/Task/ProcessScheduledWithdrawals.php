<?php

declare(strict_types=1);

namespace App\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use App\Service\AccountWithdrawService;
use App\Model\AccountWithdraw;

class ProcessScheduledWithdrawals
{
    private StdoutLoggerInterface $logger;
    private AccountWithdrawService $withdrawService;

    public function __construct(
        StdoutLoggerInterface $logger,
        AccountWithdrawService $withdrawService
    ) {
        $this->logger = $logger;
        $this->withdrawService = $withdrawService;
    }

    public function handle(): void
    {
        try {
            $this->logger->info('ProcessScheduledWithdrawals executado pelo crontab!');

            $pendingWithdrawals = AccountWithdraw::scheduledPending()->get();

            $this->logger->info(sprintf('Encontrados %d saques agendados pendentes', $pendingWithdrawals->count()));

            foreach ($pendingWithdrawals as $withdraw) {
                try {
                    $this->withdrawService->processScheduledWithdraw($withdraw);
                    $this->logger->info(sprintf('Saque %s processado com sucesso', $withdraw->id));
                } catch (\Exception $e) {
                    $this->logger->error(sprintf('Erro ao processar saque %s: %s', $withdraw->id, $e->getMessage()));
                }
            }

            $this->logger->info('Processamento de saques agendados concluído - ' . date('Y-m-d H:i:s', time()));
        } catch (\Throwable $e) {
            $this->logger->error('Erro inesperado no handle: ' . $e->getMessage());
            throw $e;
        }
    }
}