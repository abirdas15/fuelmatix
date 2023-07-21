<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $table = 'sale';
    public $timestamps = false;
    protected $hidden = [
        'user_id',
        'client_company_id',
        'note'
    ];
}
