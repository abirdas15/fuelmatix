<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProductPrice extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'company_product_price';
    public $timestamps = false;
}
