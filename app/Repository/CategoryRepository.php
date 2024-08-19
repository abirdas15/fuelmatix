<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

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
     * Save a new category based on the provided data and an existing category.
     *
     * @param array $data Data to be saved in the new category.
     * @param int $categoryId ID of the existing category used to set the parent category.
     * @param string|null $module Optional module name to associate with the new category.
     * @return Category|false Returns the saved Category model on success, or false on failure.
     */
    public static function saveCategory(array $data, int $categoryId, string $module = null)
    {
        // Retrieve the current session user.
        $sessionUser = SessionUser::getUser();

        // Find the existing category based on the given ID and the client's company ID.
        $category = Category::where('id', $categoryId)
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        // Create a new Category model.
        $categoryModel = new Category();

        // Set the name of the new category from the provided data.
        $categoryModel->name = $data['name'];

        $categoryModel->slug = strtolower($data['name']);

        // Set the parent category to the ID of the existing category.
        $categoryModel->parent_category = $category['id'];

        // Set the type of the new category to the type of the existing category.
        $categoryModel->type = $category['type'];

        // Set the optional module name if provided.
        $categoryModel->module = $module;

        // Set the module ID if provided in the data.
        $categoryModel->module_id = $data['module_id'] ?? null;

        // Set the opening balance if provided in the data.
        $categoryModel->opening_balance = $data['opening_balance'] ?? null;

        // Set any other data fields if provided.
        $categoryModel->others = $data['others'] ?? null;

        // Set the RFID if provided in the data.
        $categoryModel->rfid = $data['rfid'] ?? null;

        // Set the credit limit if provided in the data.
        $categoryModel->credit_limit = $data['credit_limit'] ?? null;

        // Set the client company ID based on the session user's company ID.
        $categoryModel->client_company_id = $sessionUser['client_company_id'];

        // Save the new category model to the database.
        // If the save operation fails, return false.
        if (!$categoryModel->save()) {
            return false;
        }

        // Update the category with additional operations if needed.
        $categoryModel->updateCategory();

        // Return the saved category model.
        return $categoryModel;
    }

    /**
     * Updates an existing category with the provided data and refreshes its hierarchy.
     *
     * This method updates the specified category's fields based on the provided data array.
     * If the update is successful, it refreshes the category's hierarchy and related fields.
     *
     * @param Category $category The category to be updated.
     * @param array $data The data to update the category with.
     * @return Category|bool The updated category, or false if the update fails.
     */
    public static function updateCategory(Category $category, array $data)
    {
        // Update the category's fields with the provided data
        $category->name = $data['name'];
        $category->others = $data['others'] ?? null;
        $category->credit_limit = $data['credit_limit'] ?? null;
        $category->module_id = $data['module_id'] ?? $category['module_id'];
        $category->opening_balance = $data['opening_balance'] ?? null;

        // Attempt to save the updated category; return false if it fails
        if (!$category->save()) {
            return false;
        }

        // Refresh the category's hierarchy and related fields
        $category->updateCategory();

        // Return the updated category
        return $category;
    }

    public static function updateAccountReceivableCategory(Category $category, array $data, $parent_id = null)
    {
        $sessionUser = SessionUser::getUser();
        if ($parent_id != null) {
            $parentCategory = Category::where('id', $parent_id)
                ->where('client_company_id', $sessionUser['client_company_id'])
                ->first();
        } else {
            $parentCategory = Category::where('slug', strtolower(AccountCategory::ACCOUNT_RECEIVABLE))
                ->where('client_company_id', $sessionUser['client_company_id'])
                ->first();

        }
        $category->name = $data['name'];
        $category->parent_category = $parentCategory->id;
        $category->others = $data['others'] ?? null;
        $category->credit_limit = $data['credit_limit'] ?? null;
        $category->module_id = $data['module_id'] ?? $category['module_id'];
        $category->opening_balance = $data['opening_balance'] ?? null;
        if (!$category->save()) {
            return false;
        }
        $category->updateCategory();
        return $category;
    }

    /**
     * Saves a new category under a specified parent group.
     *
     * @param string $categoryName The name of the new category to be created.
     * @param string $parentGroupName The name of the parent group under which the category should be saved.
     * @return Category|false The created Category object if successful, or false if any error occurs.
     */
    public static function saveCategoryByParentGroup(string $categoryName, string $parentGroupName)
    {
        // Retrieve the current session user's details.
        $sessionUser = SessionUser::getUser();

        // Find the parent group by its name and the current user's client company ID.
        $parentGroup = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower($parentGroupName))
            ->first();

        // If the parent group is not found, return false.
        if (!$parentGroup instanceof Category) {
            return false;
        }
        $lastInsertedId = DB::table('categories')->insertGetId([
            'name' => $categoryName,
            'slug' => strtolower($categoryName),
            'type' => $parentGroup['type'],
            'parent_category' => $parentGroup->id,
            'client_company_id' => $sessionUser['client_company_id']
        ]);
        $category = Category::where('id', $lastInsertedId)->first();
        $category->updateCategory();
        // Return the newly created category.
        return $category;
    }

}
