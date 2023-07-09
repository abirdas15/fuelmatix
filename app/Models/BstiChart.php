<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BstiChart extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'bsti_chart';
    public $timestamps = false;
}
