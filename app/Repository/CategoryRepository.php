<?php

namespace App\Repository;

use App\Models\Category;

class CategoryRepository
{
    /**
     * @param array $data
     * @return Category|string[]
     */
    public static function save(array $data)
    {
        $category = new Category($data);
        if ($category->save()) {
            $category->updateCategory();
            return $category;
        }
        return ['message' => 'Cannot save category.'];
    }
}
