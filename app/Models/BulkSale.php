<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkSale extends Model
{
    use HasFactory;
    protected $table = 'bulk_sales';
    public $timestamps = false;
}
