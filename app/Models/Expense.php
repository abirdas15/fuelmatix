<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $table = 'expense';
    public $timestamps = false;
    protected $appends = [
        'file_path'
    ];
    public function getFilePathAttribute(): ?string
    {
        if (!empty($this->file)) {
            return asset('uploads/'.$this->file);
        }
        return null;
    }
}
