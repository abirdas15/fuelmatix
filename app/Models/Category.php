<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    public $timestamps = false;

    protected $appends = [
        'balance_format'
    ];

    public function grandchildren()
    {
        return $this->hasMany(self::class, 'parent_category');
    }
    public function children()
    {
        return $this->grandchildren()->select('id', 'parent_category', 'category', 'balance', 'description')->with('children');
    }
    public function getBalanceFormatAttribute()
    {
        if ($this->balance != null) {
            return number_format($this->balance, 2);
        }
        return '0.00';
    }
}
