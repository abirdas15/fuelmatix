<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TankRefill extends Model
{
    use HasFactory;
    protected $table = 'tank_refill';
    public $timestamps = false;
    protected $hidden = [
        'client_company_id'
    ];
}
