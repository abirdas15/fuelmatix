<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayOrder extends Model
{
    use HasFactory;
    protected $table = 'pay_order';
    public $timestamps = false;
    protected $hidden = [
        'client_company_id'
    ];
}
