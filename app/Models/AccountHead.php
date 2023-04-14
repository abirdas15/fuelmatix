<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountHead extends Model
{
    use HasFactory;
    protected $table = 'account_heads';
    public $timestamps = false;

    public function grandchildren()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
    public function children()
    {
        return $this->grandchildren()->select('id', 'parent_id', 'name', 'balance')->with('children');
    }
}
