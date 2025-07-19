<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Otp extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'email',
        'otp_code',
        'is_verified',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'otp_code' => 'string',
            'is_verified' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }
}
