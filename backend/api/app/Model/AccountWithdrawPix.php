<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\SoftDeletes;

/**
 * @property string $id
 * @property string $account_withdraw_id
 * @property string $type
 * @property string $key
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AccountWithdrawPix extends Model
{
    use SoftDeletes;

    protected ?string $table = 'account_withdraw_pix';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $casts = [
        'id' => 'string',
        'account_withdraw_id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected array $fillable = [
        'id',
        'account_withdraw_id',
        'type',
        'key'
    ];
}