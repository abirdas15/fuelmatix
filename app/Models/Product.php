<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    public $timestamps = false;
    protected $hidden = [
        'client_company_id'
    ];

    /**
     * @param float $quantity
     * @return bool
     */
    public function updateQuantity(float $quantity): bool
    {
        $this->current_stock = $quantity;
        if (!$this->save()) {
            return false;
        }
        return true;
    }

}
