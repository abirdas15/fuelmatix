<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelAdjustmentData extends Model
{
    use HasFactory;
    protected $table = 'fuel_adjustment_data';
    public $timestamps = false;
}
