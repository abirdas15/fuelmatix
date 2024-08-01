<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nozzle extends Model
{
    use HasFactory;
    protected $table = 'nozzles';
    public $timestamps = false;

    protected $hidden = [
        'client_company_id'
    ];
    public function latestShiftSummary()
    {
        return $this->hasOne(ShiftSummary::class)->latestOfMany();
    }
}
