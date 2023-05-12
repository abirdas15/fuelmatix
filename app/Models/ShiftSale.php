<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSale extends Model
{
    use HasFactory;

    protected $table = 'shift_sale';

    public $timestamps = false;
}
