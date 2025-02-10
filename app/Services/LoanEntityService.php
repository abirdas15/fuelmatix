<?php

namespace App\Services;
use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Repository\CategoryRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanEntityService
{
    /**
     * @throws Exception
     */
    public function save(array $requestData): Category
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
        $category = CategoryRepository::saveCategory($requestData, $loanLiability['id']);
        if (!$category instanceof Category) {
            throw new Exception('Cannot save loan entity');
        }
        $category->addOpeningBalance();
        return $category;
    }

    public function list(array $filter)
    {
        $sessionUser = SessionUser::getUser();

        // Retrieve the limit for pagination, with a default value of 10 if not provided
        $limit = $filter['limit'] ?? 10;
        $keyword = $filter['keyword'] ?? '';
        $order_by = $filter['order_by'] ?? 'id';
        $order_mode = $filter['order_mode'] ?? 'DESC';

        // Retrieve the bank category associated with the user's company
        $entity = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::LOAN_LIABILITIES))
            ->first();

        // If the bank category does not exist, return a JSON response with an error message and status 400
        if (!$entity instanceof Category) {
            throw new Exception('Cannot find [entity] category.');
        }

        // Prepare the query to select id, name, and opening_balance for categories under the bank
        $result = Category::select('id', 'name', 'opening_balance')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('parent_category', $entity->id);

        // If a keyword is provided, add a filter to search within the 'name' field
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Order the results by the specified column and direction
        // Paginate the results based on the specified limit

        // Return a JSON response with the status 200 and the paginated data
        return $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
    }

    public function single(int $id)
    {
        // Retrieve the category by ID, selecting only the id, name, and opening_balance fields
        return Category::select('id', 'name', 'opening_balance')->find($id);

    }
    public function update(array $data)
    {
        $category = Category::find($data['id']);

        // If the category does not exist, return a JSON response with an error message and status 400
        if (!$category instanceof Category) {
            throw new Exception('Cannot find [entity] category.');
        }

        // Update the category with the new data
        // Update the category's fields with the provided data
        $category->name = $data['name'];
        $category->opening_balance = $data['opening_balance'] ?? null;

        // Attempt to save the updated category; return false if it fails
        if (!$category->save()) {
            throw new Exception('Cannot save [entity] category.');
        }

        // Refresh the category's hierarchy and related fields
        $category->updateCategory();

        // Return the updated category
        return $category;

    }

    public function delete(int $id)
    {
        // Find the category by its ID
        $category = Category::find($id);

        // If the category does not exist, return a JSON response with an error message and status 400
        if (!$category instanceof Category) {
            throw new Exception('Cannot find [entity] category.');
        }
        $category->deleteCategory();

        return $category;
    }
}
