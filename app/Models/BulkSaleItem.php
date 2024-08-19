<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkSaleItem extends Model
{
    use HasFactory;
    protected $table = 'bulk_sale_items';
    public $timestamps = false;
}
