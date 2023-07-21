<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleData extends Model
{
    use HasFactory;
    protected $table = 'sale_data';
    public $timestamps = false;
}
