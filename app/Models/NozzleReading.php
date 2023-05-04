<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NozzleReading extends Model
{
    use HasFactory;
    protected $table = 'nozzle_readings';
    public $timestamps = false;
    protected $hidden = [
        'client_company_id'
    ];
}
