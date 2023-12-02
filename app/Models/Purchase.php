<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $table = 'purchase';
    public $timestamps = false;
    public function purchase_item()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id', 'id')
            ->leftJoin('products', 'products.id', '=', 'purchase_item.product_id')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->select('purchase_item.*', 'products.name as product_name', 'product_types.unit');
    }
}
