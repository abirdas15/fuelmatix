<?php

namespace App\Observers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Models\Category;
use App\Repository\CategoryRepository;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function created(Category $category)
    {
        $this->saveSubCategory($category);
    }

    /**
     * Handle the Category "updated" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function updated(Category $category)
    {
        $this->updateSubCategory($category);
    }

    /**
     * Handle the Category "deleted" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function deleted(Category $category)
    {
        //
    }

    /**
     * Handle the Category "restored" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function restored(Category $category)
    {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function forceDeleted(Category $category)
    {
        //
    }

    /**
     * @param Category $category
     * @return false|void
     */
    protected function saveSubCategory(Category $category)
    {
        switch ($category->module) {
            case Module::POS_MACHINE:
                $bankExpense = Category::where('client_company_id', $category['client_company_id'])
                    ->where('slug', strtolower(AccountCategory::BANK_EXPENSE))
                    ->first();
                if (!$bankExpense instanceof Category) {
                    $bankExpense = CategoryRepository::saveCategoryByParentGroup(AccountCategory::BANK_EXPENSE, AccountCategory::EXPENSES);
                }
                $bankExpense->saveSubCategory($category);
                break;
            default:
                return false;
        }
    }
    protected function updateSubCategory(Category $category)
    {
        switch ($category->module) {
            case Module::POS_MACHINE:
                $bankExpense = Category::where('client_company_id', $category['client_company_id'])
                    ->where('slug', strtolower(AccountCategory::BANK_EXPENSE))
                    ->first();
                if (!$bankExpense instanceof Category) {
                    $bankExpense = CategoryRepository::saveCategoryByParentGroup(AccountCategory::BANK_EXPENSE, AccountCategory::EXPENSES);
                }
                $expenseCategory = Category::where('module', Module::POS_MACHINE)->where('module_id', $category->id)->first();
                if (!$expenseCategory instanceof Category) {
                    $bankExpense->saveSubCategory($category);
                } else {
                    CategoryRepository::updateCategory($expenseCategory, ['name' => $category->name]);
                }
        }
    }
}
