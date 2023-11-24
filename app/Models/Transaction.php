<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'logo',
        'website',
        'account_balance',
        'account_limit',
        'account_name',
        'company_owner',
    ];

    //   a transaction belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
