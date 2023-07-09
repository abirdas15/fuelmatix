<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TankLog extends Model
{
    use HasFactory;
    protected $table = 'tank_log';
    public $timestamps = false;
}
