<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $table = 'expense';
    public $timestamps = false;
    protected $hidden = [
        'date'
    ];
    protected $appends = [
        'file_path'
    ];
    public function getFilePathAttribute()
    {
        if ($this->file != null) {
            return asset('uploads/'.$this->file);
        }
    }
}
