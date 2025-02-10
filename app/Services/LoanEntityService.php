<?php

namespace App\Services;
use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Repository\CategoryRepository;
use Exception;

class LoanEntityService
{
    /**
     * @throws Exception
     */
    public function save(array $requestData)
    {
        // Get the current authenticated session user
        $sessionUser = SessionUser::getUser();

        // Retrieve the loan liability category associated with the user's company
        $loanLiability = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::LOAN_LIABILITIES))
            ->first();

        if (!$loanLiability instanceof Category) {
            $loanLiability = CategoryRepository::saveCategoryByParentGroup(AccountCategory::LOAN_LIABILITIES, AccountCategory::CURRENT_LIABILITIES);
        }
        // Save the new category in the repository under the bank category
        $category = CategoryRepository::saveCategory($requestData, $loanLiability['id']);

        // If the category could not be saved, return a JSON response with an error message and status 400
        if (!$category instanceof Category) {
            throw new Exception('Cannot save loan entity');
        }

        // Add the opening balance to the saved category if it was specified
        $category->addOpeningBalance();

        return $category;
    }
}
