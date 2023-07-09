<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transactions';
    public $timestamps = false;
    protected $appends = [
        'file_path'
    ];
    public function getFilePathAttribute()
    {
        if ($this->file != null) {
            return asset('uploads/'.$this->file);
        }
        return null;
    }
}
