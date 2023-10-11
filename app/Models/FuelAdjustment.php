<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelAdjustment extends Model
{
    use HasFactory;
    protected $table = 'fuel_adjustment';
    public $timestamps = false;
}
