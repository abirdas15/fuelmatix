<?php

namespace App\Models;

use App\Common\AccountCategory;
use App\Common\FuelMatixCategoryType;
use App\Helpers\SessionUser;
use App\Repository\TransactionRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    public $timestamps = false;
    protected $guarded = ['id'];

    /**
     * Adds an opening balance to an account.
     *
     * This function first deletes any existing opening balance for the account
     * and then adds a new opening balance if specified.
     *
     * @return bool True if the opening balance was successfully added, false otherwise.
     */
    public function addOpeningBalance(): bool
    {
        // Get the currently authenticated session user
        $sessionUser = SessionUser::getUser();

        // Delete the existing opening balance for the account
        $deleteResponse = $this->deleteOpeningBalance();

        // Check if the deletion was successful
        if ($deleteResponse) {
            // If an opening balance is specified
            if (!empty($this['opening_balance'])) {
                // Retrieve the category with the 'retain earning' slug for the current user's company
                $retainEarning = Category::where('client_company_id', $sessionUser['client_company_id'])
                    ->where('slug', strtolower(AccountCategory::RETAIN_EARNING))
                    ->first();

                // If the 'retain earning' category exists
                if ($retainEarning instanceof Category) {
                    $transactionData = [];
                    // Determine debit and credit amounts based on the category type
                    if ($this['type'] == FuelMatixCategoryType::ASSET || $this['type'] == FuelMatixCategoryType::EXPENSE) {
                        // For ASSET and EXPENSE: debit the opening balance and set credit to 0
                        $debitAmount = $this['opening_balance'];
                        $creditAmount = 0;
                    } elseif ($this['type'] == FuelMatixCategoryType::LIABILITIES || $this['type'] == FuelMatixCategoryType::EQUITY || $this['type'] == FuelMatixCategoryType::INCOME) {
                        // For LIABILITIES, EQUITY, and INCOME: credit the opening balance and set debit to 0
                        $debitAmount = 0;
                        $creditAmount = $this['opening_balance'];
                    }

                    // Prepare transaction data only if amounts are set
                    if (isset($debitAmount) && isset($creditAmount)) {
                        $transactionData = [
                            [
                                'date' => "1970-01-01",
                                'account_id' => $this['id'],
                                'debit_amount' => $debitAmount,
                                'credit_amount' => $creditAmount,
                                'opening_balance' => 1
                            ],
                            [
                                'date' => "1970-01-01",
                                'account_id' => $retainEarning['id'],
                                'debit_amount' => $creditAmount,
                                'credit_amount' => $debitAmount,
                                'opening_balance' => 1
                            ],
                        ];
                    }

                    // Save the transaction data if not empty
                    if (!empty($transactionData)) {
                        TransactionRepository::saveTransaction($transactionData);
                    }

                    // Return true indicating success
                    return true;
                }
            }
        }

        // Return false if the opening balance was not added
        return false;
    }


    /**
     * Updates the category's hierarchy and ID arrays based on the current category's information.
     *
     * @return bool Returns true if the category was successfully updated, otherwise false.
     */
    public function updateCategory(): bool
    {
        // Initialize arrays to hold category hierarchy and IDs
        $categoryHierarchy = [];
        $categoryIds = [];

        // Check if there is a parent category and retrieve its data
        if (!empty($this->parent_category)) {
            $parentCategory = Category::where('id', $this->parent_category)->first();

            if ($parentCategory) {
                $categoryHierarchy = json_decode($parentCategory->category_hericy, true) ?? [];
                $categoryIds = json_decode($parentCategory->category_ids, true) ?? [];
            }
        }

        // Add the current category's name and ID to the arrays
        $categoryHierarchy[] = $this->name;
        $categoryIds[] = $this->id;

        // Find the current category by ID and update its fields
        $category = Category::find($this->id);
        if ($category) {
            $category->category_hericy = json_encode($categoryHierarchy);
            $category->category_ids = json_encode($categoryIds);

            // Save the updated category and return true if successful
            return $category->save();
        }

        // Return false if the category was not found
        return false;
    }



    /**
     * Deletes the opening balance for the account.
     *
     * This function finds and deletes any transaction associated with the account
     * that has been marked as an opening balance.
     *
     * @return bool True after attempting to delete the opening balance.
     */
    public function deleteOpeningBalance(): bool
    {
        // Get the account ID from the current instance
        $categoryId = $this['id'];

        // Find the transaction associated with this account that is marked as an opening balance
        $transaction = Transaction::where('account_id', $categoryId)
            ->where('opening_balance', 1)
            ->first();

        // If such a transaction exists
        if ($transaction instanceof Transaction) {
            // Delete the transaction and any related transactions linked by 'linked_id'
            Transaction::where('id', $transaction['id'])
                ->orWhere('linked_id', $transaction['id'])
                ->delete();
        }

        // Return true indicating that the process completed (whether or not a transaction was deleted)
        return true;
    }
    public function deleteCategory()
    {
        // Get the IDs of transactions associated with this category
        $transactionId = Transaction::select('id')
            ->where('account_id', $this['id'])
            ->pluck('id')
            ->toArray();

        // Delete the transactions associated with this category
        Transaction::where('id', $this['id'])
            ->orWhereIn('linked_id', $transactionId)
            ->delete();

        // Delete the opening balance associated with this category
        $this->deleteOpeningBalance();

        // Delete the category itself
        Category::where('id', $this['id'])->delete();
    }

    /**
     * Check if the available balance for the account is greater than or equal to a specified amount.
     *
     * @param float $amount The amount to compare against the available balance.
     * @return bool Returns true if the available balance is greater than or equal to the specified amount; otherwise, returns false.
     */
    public function checkAvailableBalance(float $amount): bool
    {
        // Retrieve the total debit and credit amounts for the account
        $transaction = Transaction::select(DB::raw('SUM(debit_amount) as debit_amount'), DB::raw('SUM(credit_amount) as credit_amount'))
            ->where('account_id', $this['id'])
            ->first();

        // If no transactions are found for the account, return false (no balance available)
        if (!$transaction) {
            return false;
        }

        $totalAmount = 0;

        // Calculate the total available amount based on the account type
        if ($this['type'] == FuelMatixCategoryType::ASSET || $this['type'] == FuelMatixCategoryType::EXPENSE) {
            // For ASSET or EXPENSE types, available balance is calculated as debit amount minus credit amount
            $totalAmount = $transaction['debit_amount'] - $transaction['credit_amount'];
        } else if ($this['type'] == FuelMatixCategoryType::LIABILITIES || $this['type'] == FuelMatixCategoryType::EQUITY || $this['type'] == FuelMatixCategoryType::INCOME) {
            // For LIABILITIES, EQUITY, or INCOME types, available balance is calculated as credit amount minus debit amount
            $totalAmount = $transaction['credit_amount'] - $transaction['debit_amount'];
        }

        // Compare the available balance with the specified amount and return the result
        return $totalAmount >= $amount;
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

    /**
     * Saves a subcategory under the current category and updates its hierarchy.
     *
     * This method inserts a new subcategory into the `categories` table and returns
     * the newly created category with its hierarchy updated.
     *
     * @param Category $category The subcategory object to be saved.
     * @return Category The saved subcategory with updated hierarchy.
     */
    public function saveSubCategory(Category $category): Category
    {
        // Insert the new subcategory and get its ID
        $lastInsertedId = DB::table('categories')->insertGetId([
            'name' => $category->name,
            'slug' => strtolower($category->name),
            'parent_category' => $this['id'],
            'type' => $this['type'],
            'module' => $category['module'] ?? null,
            'module_id' => $category['id'] ?? null,
        ]);

        // Retrieve the newly inserted category by its ID
        $category = Category::where('id', $lastInsertedId)->first();

        // Update the hierarchy and category IDs for the new subcategory
        $category->updateCategory();

        // Return the saved subcategory
        return $category;
    }

}
