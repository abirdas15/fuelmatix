<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DummySale extends Model
{
    use HasFactory;
    protected $table = 'dummy_sale';
    public $timestamps = false;
    public function dummy_sale_data()
    {
        return $this->hasMany(DummySaleData::class, 'sale_id', 'id');
    }
}
