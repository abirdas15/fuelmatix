<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceTransfer extends Model
{
    use HasFactory;
    protected $table = 'balance_transfer';
    public $timestamps = false;
    protected $hidden = [
        'client_company_id'
    ];
}
