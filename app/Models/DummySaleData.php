<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DummySaleData extends Model
{
    use HasFactory;
    protected $table = 'dummy_sale_data';
    public $timestamps = false;
}
