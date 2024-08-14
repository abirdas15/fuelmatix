<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TankRefillTotal extends Model
{
    use HasFactory;
    protected $table = 'tank_refill_total';
    public $timestamps = false;
}
