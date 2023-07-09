<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tank extends Model
{
    use HasFactory;
    protected $table = 'tank';
    public $timestamps = false;
    public function last_reading()
    {
        return $this->hasOne(TankLog::class, 'tank_id', 'id');
    }
}
