<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispenser extends Model
{
    use HasFactory;
    protected $table = 'dispensers';
    public $timestamps = false;

    public function nozzle()
    {
        return $this->hasMany(Nozzle::class, 'dispenser_id', 'id');
    }

    protected $hidden = [
        'client_company_id'
    ];
}
