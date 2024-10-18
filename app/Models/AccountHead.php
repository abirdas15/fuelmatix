<?php

namespace App\Models;

use App\Helpers\SessionUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountHead extends Model
{
    use HasFactory;
    protected $table = 'account_heads';
    public $timestamps = false;

    protected $appends = [
        'balance_format'
    ];

    public function grandchildren()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
    public function children()
    {
        return $this->grandchildren()->select('id', 'parent_id', 'name', 'balance')->with('children');
    }
    public function getBalanceFormatAttribute()
    {
        $sessionUser = SessionUser::getUser();
        if ($this->balance != null) {
            return number_format($this->balance, $sessionUser['currency_precision']);
        }
        return '0.00';
    }
}
