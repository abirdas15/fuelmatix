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
    protected $appends = [
        'sale_between'
    ];
    public function getSaleBetweenAttribute()
    {
        return TankRefillHistory::where('tank_refill_id', $this->id)->sum('sale');
    }
}
