<?php

namespace App\Repository;

use App\Helpers\SessionUser;
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

    /**
     * @param array $data
     * @param int $categoryId
     * @param string|null $module
     * @return Category|false
     */
    public static function saveCategory(array $data, int $categoryId, string $module = null)
    {
        $sessionUser = SessionUser::getUser();
        $category = Category::where('id', $categoryId)
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        $categoryModel = new Category();
        $categoryModel->name = $data['name'];
        $categoryModel->parent_category = $category['id'];
        $categoryModel->type = $category['type'];
        $categoryModel->module = $module;
        $categoryModel->module_id = $data['module_id'] ?? null;
        $categoryModel->others = $data['others'] ?? null;
        $categoryModel->rfid = $data['rfid'] ?? null;
        $categoryModel->credit_limit = $data['credit_limit'] ?? null;
        $categoryModel->client_company_id = $sessionUser['client_company_id'];
        if (!$categoryModel->save()) {
            return false;
        }
        $categoryModel->updateCategory();
        return $categoryModel;
    }

    /**
     * @param Category $category
     * @param array $data
     * @return Category|false
     */
    public static function updateCategory(Category $category, array $data)
    {
        $category->name = $data['name'];
        $category->others = $data['others'] ?? null;
        $category->credit_limit = $data['credit_limit'] ?? null;
        $category->module_id = $data['module_id'] ?? $category['module_id'];
        if (!$category->save()) {
            return false;
        }
        $category->updateCategory();
        return $category;
    }
}
