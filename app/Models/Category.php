<?php

namespace App\Models;

use App\Helpers\MybosTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    public $timestamps = false;
    protected $guarded = ['id'];
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        self::saving(function ($model) {

        });

        static::created(function($model)
        {

        });

        self::updating(function (&$model) {

        });
    }


    public function grandchildren()
    {
        return $this->hasMany(self::class, 'parent_category');
    }
    public function children()
    {
        return $this->grandchildren()->select('id', 'parent_category', 'category', 'balance', 'description', 'category_ids', 'type')->with('children');
    }

    public function grandparent()
    {
        return $this->belongsTo(self::class, 'parent_category');
    }
    public function parent()
    {
        return $this->grandparent()->with('parent');
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'account_id', 'id');
    }
}
