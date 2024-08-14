<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixCategoryType;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BalanceSheetController extends Controller
{
    /**
     * Handles a request to retrieve and calculate financial data.
     *
     * @param Request $request The HTTP request object containing input data.
     * @return JsonResponse The JSON response containing the calculated financial data or errors.
     */
    public function get(Request $request): JsonResponse
    {
        // Validate the request to ensure the 'date' field is provided.
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);

        // If validation fails, return a JSON response with errors and a status of 500.
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the current session user's data.
        $sessionUser = SessionUser::getUser();

        // Query to retrieve and calculate the sum of debit and credit amounts, grouped by account_id.
        $transactions = Transaction::select(
            DB::raw('SUM(debit_amount) as debit_amount'),
            DB::raw('SUM(credit_amount) as credit_amount'),
            'category_ids'
        )
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->where('date', '<=', $request->input('date'))
            ->groupBy('account_id')
            ->get()
            ->toArray();

        // Calculate the total assets, liabilities, and equity based on the transactions.
        $assets = self::getAssets($transactions);
        $liabilities = self::getLiabilities($transactions);
        $equity = self::getEquity($transactions);

        // Calculate retained earnings as of the provided date.
        $retain_earning = self::getRetainEarning($request->input('date'));

        // Calculate total equity by adding retained earnings to the sum of equity.
        $total_equity = $retain_earning + self::getTotalAmount($equity);

        // Calculate the total liabilities.
        $total_liabilities = self::getTotalAmount($liabilities);

        // Prepare the result array with calculated values.
        $result = [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'retain_earning' => $retain_earning,
            'total_asset' => self::getTotalAmount($assets),
            'total_liabilities' => $total_liabilities,
            'total_equity' => $total_equity,
            'total_equity_and_liabilities' => $total_equity + $total_liabilities,
        ];

        // Return a JSON response with the calculated data and a status of 200.
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    public static function getTotalAmount($transactions, $total = 0)
    {
        foreach ($transactions as $data) {
            $total = $total + $data['balance'];
        }
        return $total;
    }

    /**
     * Calculates the retained earnings up to a specified date.
     *
     * Retained earnings are calculated as the total income minus the total expenses up to the given date.
     *
     * @param string $date The date up to which retained earnings are to be calculated.
     * @return float|int The retained earnings value, which is the difference between total income and total expenses.
     */
    public static function getRetainEarning(string $date)
    {
        // Retrieve the total income up to the specified date using the getTransactionAmount method.
        // The 'income' type is used to filter the transactions.
        $income = self::getTransactionAmount($date, 'income');

        // Sum all the income amounts retrieved.
        $income = array_sum($income);

        // Retrieve the total expenses up to the specified date using the getTransactionAmount method.
        // The 'expenses' type is used to filter the transactions.
        $expense = self::getTransactionAmount($date, 'expenses');

        // Sum all the expense amounts retrieved.
        $expense = array_sum($expense);

        // Calculate retained earnings by subtracting total expenses from total income.
        return $income - $expense;
    }
    /**
     * Retrieves and calculates the equity categories for the current user's company.
     *
     * Equity categories represent the ownership interest in the company, and this method
     * gathers all equity-related categories, along with their balances, and updates
     * these balances based on the provided transaction data.
     *
     * @param array $transactions An array of transactions used to update category balances.
     * @return array The updated list of equity categories with their respective balances.
     */
    public static function getEquity(array $transactions): array
    {
        // Retrieve the current session user's details.
        $sessionUser = SessionUser::getUser();

        // Fetch the equity categories for the user's company. The categories retrieved are:
        $categories = Category::select('id', 'name', 'balance', 'parent_category', 'description', 'category_ids', 'type')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['children' => function($q) {
                // Select specific fields for child categories.
                $q->select('id', 'name', 'parent_category', 'balance', 'description', 'category_ids', 'type');
            }])
            ->where('type', 'equity')
            ->whereNull('parent_category')
            ->get()
            ->toArray();

        return CategoryController::updateCategoryBalance($categories, $transactions);
    }


    /**
     * Retrieves and calculates the asset categories with updated balances based on the provided transactions.
     *
     * @param array $transactions The array of transactions to use for calculating asset balances.
     * @return array The array of asset categories with updated balances.
     */
    public static function getAssets(array $transactions): array
    {
        // Retrieve the current session user's data.
        $sessionUser = SessionUser::getUser();

        // Query to retrieve asset categories for the user's company, including their child categories.
        $categories = Category::select('id', 'name', 'balance', 'parent_category', 'description', 'category_ids', 'type')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['children' => function($q) {
                $q->select('id', 'name', 'parent_category', 'balance', 'description', 'category_ids', 'type');
            }])
            ->where('type', 'assets')  // Filter for categories of type 'assets'.
            ->whereNull('parent_category')  // Filter for top-level categories (no parent).
            ->get()
            ->toArray();

        // Update the balance of each category based on the provided transactions and return the result.
        return CategoryController::updateCategoryBalance($categories, $transactions);
    }

    /**
     * Retrieves and calculates the liability categories with updated balances based on the provided transactions.
     *
     * @param array $transactions The array of transactions to use for calculating liability balances.
     * @return array The array of liability categories with updated balances.
     */
    public static function getLiabilities(array $transactions): array
    {
        // Retrieve the current session user's data.
        $sessionUser = SessionUser::getUser();

        // Query to retrieve liability categories for the user's company, including their child categories.
        $categories = Category::select('id', 'name', 'balance', 'parent_category', 'description', 'category_ids', 'type')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['children' => function($q) {
                $q->select('id', 'name', 'parent_category', 'balance', 'description', 'category_ids', 'type');
            }])
            ->where('type', 'liabilities')  // Filter for categories of type 'liabilities'.
            ->whereNull('parent_category')  // Filter for top-level categories (no parent).
            ->get()
            ->toArray();

        // Update the balance of each liability category based on the provided transactions and return the result.
        return CategoryController::updateCategoryBalance($categories, $transactions);
    }


    /**
     * Builds a hierarchical tree structure from a flat array of elements based on parent-child relationships.
     *
     * @param array $elements The flat array of elements to be organized into a tree structure.
     * @param int $parentId The ID of the parent element to start building the tree from. Defaults to 0 (top-level).
     * @return array The hierarchical tree structure.
     */
    public static function buildTree(array $elements, int $parentId = 0): array
    {
        $branch = [];

        // Loop through each element in the array.
        foreach ($elements as $element) {
            // Check if the current element's parent_category matches the provided parentId.
            if ($element['parent_category'] == $parentId) {
                // Recursively build the tree for the current element's children.
                $children = self::buildTree($elements, $element['id']);

                // If the element has children, add them to the 'children' key.
                if ($children) {
                    $element['children'] = $children;
                }

                // Add the current element (with its children, if any) to the branch.
                $branch[] = $element;
            }
        }

        // Return the constructed branch (or subtree).
        return $branch;
    }


    /**
     * Adds the balance amounts from the transactions to the corresponding categories.
     *
     * @param array $categories The array of categories to which the balances will be added.
     * @param array $transactions The array of transactions where the key is the category ID and the value is the balance.
     * @return array The array of categories with updated balances.
     */
    public static function addCategoryAmount(array $categories, array $transactions): array
    {
        // Loop through each category in the provided categories array.
        foreach ($categories as &$category) {
            // Update the category's balance with the corresponding transaction balance.
            // If the category's ID exists in the transactions array, use that value.
            // Otherwise, set the balance to 0.
            $category['balance'] = $transactions[$category['id']] ?? 0;
        }

        // Return the updated categories array.
        return $categories;
    }


    /**
     * Retrieves the transaction amounts for a given date and category type, grouped by account ID.
     *
     * @param string $date The date up to which transactions should be considered.
     * @param string $type The type of category (e.g., asset, liabilities, equity, income, expense).
     * @return array An associative array where the keys are account IDs and the values are the corresponding balances.
     */
    public static function getTransactionAmount(string $date, string $type): array
    {
        // Determine the SQL calculation for balance based on the category type.
        if ($type == FuelMatixCategoryType::ASSET) {
            // For assets, balance is calculated as debit amount minus credit amount.
            $select = DB::raw('SUM(debit_amount - credit_amount) as balance');
        } else if ($type == FuelMatixCategoryType::LIABILITIES || $type == FuelMatixCategoryType::EQUITY) {
            // For liabilities and equity, balance is calculated as credit amount minus debit amount.
            $select = DB::raw('SUM(credit_amount - debit_amount) as balance');
        } else if ($type == FuelMatixCategoryType::INCOME) {
            // For income, balance is also calculated as credit amount minus debit amount.
            $select = DB::raw('SUM(credit_amount - debit_amount) as balance');
        } else if ($type == FuelMatixCategoryType::EXPENSE) {
            // For expenses, balance is calculated as debit amount minus credit amount.
            $select = DB::raw('SUM(debit_amount - credit_amount) as balance');
        }

        // Execute the query to get the transaction amounts grouped by account ID.
        $result = Transaction::select('account_id', $select)
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('date', '<=', $date)
            ->where('categories.type', $type)
            ->groupBy('account_id')
            ->get()
            ->toArray();

        // Prepare the return value: an associative array of account IDs and their corresponding balances.
        $rv = [];
        foreach ($result as $data) {
            $rv[$data['account_id']] = $data['balance'];
        }

        // Return the associative array of balances.
        return $rv;
    }


}
