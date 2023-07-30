<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSaleTransaction extends Model
{
    use HasFactory;
    protected $table = 'shift_sale_transaction';
    public $timestamps = false;
    protected $guarded = ['id'];
}
