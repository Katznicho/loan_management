<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
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

    //a user can have many loans
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
