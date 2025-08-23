<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $account_id
 * @property float $amount
 * @property string $method
 * @property bool $scheduled
 * @property \Carbon\Carbon $scheduled_for
 * @property bool $done
 * @property bool $error
 * @property string $error_reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $processed_at
 */
class AccountWithdraw extends Model
{
    protected ?string $table = 'account_withdraw';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'amount' => 'float',
        'scheduled' => 'boolean',
        'done' => 'boolean',
        'error' => 'boolean',
        'scheduled_for' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected array $fillable = [
        'account_id',
        'amount',
        'method',
        'scheduled',
        'scheduled_for',
        'done',
        'error',
        'error_reason',
        'processed_at'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function pixDetails()
    {
        return $this->hasOne(AccountWithdrawPix::class);
    }

    public function scopeScheduledPending($query)
    {
        return $query->where('scheduled', true)
                    ->where('done', false)
                    ->where('error', false)
                    ->where('scheduled_for', '<=', Carbon::now());
    }

    public function markAsProcessed()
    {
        $this->done = true;
        $this->processed_at = Carbon::now();
        $this->save();
    }

    public function markAsFailed(string $reason)
    {
        $this->error = true;
        $this->error_reason = $reason;
        $this->processed_at = Carbon::now();
        $this->save();
    }
}