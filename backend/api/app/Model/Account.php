<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Utils\Str;
use Hyperf\Database\Model\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;
    protected ?string $table = 'account';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $casts = [
        'balance' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected array $fillable = ['id', 'name', 'balance'];
    protected array $attributes = ['balance' => 0.00];

    public function scopeWithCreatedAt($query, string $createdAt)
    {
        return $query->whereDate('created_at', $createdAt);
    }

    public function scopeByName($query, string $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }
}
