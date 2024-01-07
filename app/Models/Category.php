<?php

namespace App\Models;

use App\Helpers\MybosTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    public $timestamps = false;
    protected $guarded = ['id'];

    public function updateCategory(): bool
    {
        $category_hericy = [];
        $category_ids = [];
        if (!empty($this->parent_category)) {
            $parentCategory = Category::where('id', $this->parent_category)->first();
            $category_hericy = json_decode($parentCategory['category_hericy']);
            $category_ids = json_decode($parentCategory['category_ids']);
        }
        $category_hericy[] = $this->name;
        $category_ids[] = $this->id;
        $category = Category::find($this->id);
        $category->category_hericy = $category_hericy;
        $category->category_ids = $category_ids;
        if ($category->save()) {
            return true;
        }
        return false;
    }
    public function deleteOpeningBalance(): bool
    {
        $categoryId = $this['id'];
        Transaction::where('opening_balance', 1)
            ->where(function($q) use ($categoryId) {
                $q->where('linked_id', $categoryId)
                    ->orWhere('account_id', $categoryId);
            })->delete();
        return true;
    }
    /**
     * @param array $productPrices
     * @return bool
     */
    public function saveProductPrice(array $productPrices): bool
    {
        CompanyProductPrice::where('company_id', $this['id'])->delete();
        $productPriceData = [];
        foreach ($productPrices as $product) {
            if (!empty($product['product_id']) && !empty($product['price'])) {
                $productPriceData[] = [
                    'product_id' => $product['product_id'],
                    'price' => $product['price'],
                    'company_id' => $this['id'],
                    'client_company_id' => $this['client_company_id']
                ];
            }
        }
        if (count($productPriceData) > 0) {
            CompanyProductPrice::insert($productPriceData);
        }
        return true;
    }



    public function grandchildren()
    {
        return $this->hasMany(self::class, 'parent_category');
    }
    public function children()
    {
        return $this->grandchildren()->select('id', 'parent_category', 'name', 'balance', 'description', 'category_ids', 'type')->with('children');
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
    public function product_price()
    {
        return $this->hasMany(CompanyProductPrice::class, 'company_id', 'id');
    }
}
