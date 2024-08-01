<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftTotal extends Model
{
    use HasFactory;
    protected $table = 'shift_total';
    public $timestamps = false;
}
