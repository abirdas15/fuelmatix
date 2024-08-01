<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSale extends Model
{
    use HasFactory;

    protected $table = 'shift_sale';

    public $timestamps = false;
    protected $hidden = [
        'user_id',
    ];
    public function shift_summary()
    {
        return $this->hasMany(ShiftSummary::class, 'shift_sale_id', 'id');
    }
}
