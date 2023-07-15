<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TankRefillHistory extends Model
{
    use HasFactory;
    protected $table = 'tank_refill_history';
    public $timestamps = false;
}
