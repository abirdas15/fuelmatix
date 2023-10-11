<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayOrderData extends Model
{
    use HasFactory;
    protected $table = 'pay_order_data';
    public $timestamps = false;
}
