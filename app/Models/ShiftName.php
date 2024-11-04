<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftName extends Model
{
    use HasFactory;
    protected $table = 'shift_names';
    public $timestamps = false;
}
