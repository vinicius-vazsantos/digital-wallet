<?php

declare(strict_types=1);

use Hyperf\Crontab\Crontab;

return [
    'enable' => true,
    'crontab' => [
        (new Crontab())
        ->setName('ProcessScheduledWithdrawals')
        ->setRule('* * * * *')
        ->setCallback([App\Task\ProcessScheduledWithdrawals::class, 'handle'])
        ->setMemo('Processa saques agendados pendentes'),
    ],
];
