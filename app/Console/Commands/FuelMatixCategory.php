<?php

namespace App\Console\Commands;

use App\Common\AccountCategory;
use App\Common\FuelMatixCategoryType;
use App\Common\Module;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Repository\CategoryRepository;
use Illuminate\Console\Command;

class FuelMatixCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fuel:matix:category
                            {companyId : The id of the company in Fuel Matix}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Default Account Head';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param array $data
     * @return Category|false
     */
    public  function saveCategory(array $data)
    {
        $category = new Category();
        $category->name = $data['name'];
        $category->slug = strtolower($data['name']);
        $category->parent_category = $data['parent_category'] ?? null;
        $category->type = $data['type'];
        $category->default = 1;
        $category->client_company_id = $data['client_company_id'];
        if (!$category->save()) {
            $this->warn('Cannot save category...');
            return false;
        }
        if (!$category->updateCategory()) {
            $this->warn('Cannot save category tree...');
            return false;
        }
        return $category;
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $companyId = $this->argument('companyId');
        $clientCompany = ClientCompany::where('id', $companyId)->get()->first();
        if (!$clientCompany instanceof ClientCompany) {
            $this->warn('Cannot find company...');
            return 0;
        }
        Category::where('client_company_id', $clientCompany->id)->delete();
        $this->info('Category: '. AccountCategory::ASSETS);
        $assetCategory = $this->saveCategory([
            'name' => AccountCategory::ASSETS,
            'type' => FuelMatixCategoryType::ASSET,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$assetCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::ASSETS.']...');
        }
        $this->info('Successfully Save '. AccountCategory::ASSETS);

        $this->info('Category: '. AccountCategory::CURRENT_ASSETS);
        $currentAssetCategory = $this->saveCategory([
            'name' => AccountCategory::CURRENT_ASSETS,
            'parent_category' => $assetCategory['id'],
            'type' => FuelMatixCategoryType::ASSET,
            'default' => 1,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$currentAssetCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::CURRENT_ASSETS.']...');
        }
        $this->info('Successfully Save '. AccountCategory::CURRENT_ASSETS);

        $this->info('Category: '. AccountCategory::ACCOUNT_RECEIVABLE);
        $accountReceivableCategory = $this->saveCategory([
            'name' => AccountCategory::ACCOUNT_RECEIVABLE,
            'parent_category' => $currentAssetCategory['id'],
            'type' => FuelMatixCategoryType::ASSET,
            'default' => 1,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$accountReceivableCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::ACCOUNT_RECEIVABLE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::ACCOUNT_RECEIVABLE);

        $this->info('Category: '. AccountCategory::CASH_IN_HAND);
        $cashInHandCategory = $this->saveCategory([
            'name' => AccountCategory::CASH_IN_HAND,
            'parent_category' => $currentAssetCategory['id'],
            'type' => FuelMatixCategoryType::ASSET,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$cashInHandCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::CASH_IN_HAND.']...');
        }
        $this->info('Successfully Save '. AccountCategory::CASH_IN_HAND);


        $this->info('Category: '. AccountCategory::STOCK_IN_HAND);
        $stockInHandCategory = $this->saveCategory([
            'name' => AccountCategory::STOCK_IN_HAND,
            'parent_category' => $currentAssetCategory['id'],
            'type' => FuelMatixCategoryType::ASSET,
            'default' => 1,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$stockInHandCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::STOCK_IN_HAND.']...');
        }
        $this->info('Successfully Save '. AccountCategory::STOCK_IN_HAND);


        $this->info('Category: '. AccountCategory::BANK);
        $bankCategory = $this->saveCategory([
            'name' => AccountCategory::BANK,
            'parent_category' => $currentAssetCategory['id'],
            'type' => FuelMatixCategoryType::ASSET,
            'default' => 1,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$bankCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::BANK.']...');
        }
        $this->info('Successfully Save '. AccountCategory::BANK);

        $this->info('Category: '. AccountCategory::POS_MACHINE);
        $posMachineCategory = $this->saveCategory([
            'name' => AccountCategory::POS_MACHINE,
            'parent_category' => $currentAssetCategory['id'],
            'type' => FuelMatixCategoryType::ASSET,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$posMachineCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::POS_MACHINE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::POS_MACHINE);


        $this->info('Category: '. AccountCategory::UN_AUTHORIZED_BILL);
        $unAuthorizedBillCategory = $this->saveCategory([
            'name' => AccountCategory::UN_AUTHORIZED_BILL,
            'parent_category' => $currentAssetCategory['id'],
            'type' => FuelMatixCategoryType::ASSET,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$unAuthorizedBillCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::UN_AUTHORIZED_BILL.']...');
        }
        $this->info('Successfully Save '. AccountCategory::UN_AUTHORIZED_BILL);

        $this->info('Category: '. AccountCategory::CASH);
        $cashCategory = $this->saveCategory([
            'name' => AccountCategory::CASH,
            'parent_category' => $cashInHandCategory['id'],
            'type' => FuelMatixCategoryType::ASSET,
            'default' => 1,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$cashCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::CASH.']...');
        }
        $this->info('Successfully Save '. AccountCategory::CASH);

        $this->info('Category: '. AccountCategory::LIABILITIES);
        $liabilityCategory = $this->saveCategory([
            'name' => AccountCategory::LIABILITIES,
            'parent_category' => null,
            'type' => FuelMatixCategoryType::LIABILITIES,
            'default' => 1,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$liabilityCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::LIABILITIES.']...');
        }
        $this->info('Successfully Save '. AccountCategory::LIABILITIES);


        $this->info('Category: '. AccountCategory::CURRENT_LIABILITIES);
        $currentLiabilityCategory = $this->saveCategory([
            'name' => AccountCategory::CURRENT_LIABILITIES,
            'parent_category' => $liabilityCategory['id'],
            'type' => FuelMatixCategoryType::LIABILITIES,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$currentLiabilityCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::CURRENT_LIABILITIES.']...');
        }
        $this->info('Successfully Save '. AccountCategory::CURRENT_LIABILITIES);

        $this->info('Category: '. AccountCategory::ACCOUNT_PAYABLE);
        $accountPayableCategory = $this->saveCategory([
            'name' => AccountCategory::ACCOUNT_PAYABLE,
            'parent_category' => $currentLiabilityCategory['id'],
            'type' => FuelMatixCategoryType::LIABILITIES,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$accountPayableCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::ACCOUNT_PAYABLE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::ACCOUNT_PAYABLE);


        $this->info('Category: '. AccountCategory::UN_EARNED_REVENUE);
        $unEarnRevenueCategory = $this->saveCategory([
            'name' => AccountCategory::UN_EARNED_REVENUE,
            'parent_category' => $liabilityCategory['id'],
            'type' => FuelMatixCategoryType::LIABILITIES,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$unEarnRevenueCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::UN_EARNED_REVENUE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::UN_EARNED_REVENUE);



        $this->info('Category: '. AccountCategory::EQUITY);
        $equityCategory = $this->saveCategory([
            'name' => AccountCategory::EQUITY,
            'parent_category' => null,
            'type' => FuelMatixCategoryType::EQUITY,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$equityCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::EQUITY.']...');
        }
        $this->info('Successfully Save '. AccountCategory::EQUITY);


        $this->info('Category: '. AccountCategory::INCOME);
        $incomeCategory = $this->saveCategory([
            'name' => AccountCategory::INCOME,
            'parent_category' => null,
            'type' => FuelMatixCategoryType::INCOME,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$incomeCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::INCOME.']...');
        }
        $this->info('Successfully Save '. AccountCategory::INCOME);

        $this->info('Category: '. AccountCategory::DIRECT_INCOME);
        $directIncomeCategory = $this->saveCategory([
            'name' => AccountCategory::DIRECT_INCOME,
            'parent_category' => $incomeCategory['id'],
            'type' => FuelMatixCategoryType::INCOME,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$directIncomeCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::DIRECT_INCOME.']...');
        }
        $this->info('Successfully Save '. AccountCategory::DIRECT_INCOME);


        $this->info('Category: '. AccountCategory::IN_DIRECT_INCOME);
        $indirectIncomeCategory = $this->saveCategory([
            'name' => AccountCategory::IN_DIRECT_INCOME,
            'parent_category' => $incomeCategory['id'],
            'type' => FuelMatixCategoryType::INCOME,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$indirectIncomeCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::IN_DIRECT_INCOME.']...');
        }
        $this->info('Successfully Save '. AccountCategory::IN_DIRECT_INCOME);


        $this->info('Category: '. AccountCategory::EXPENSES);
        $expenseCategory = $this->saveCategory([
            'name' => AccountCategory::EXPENSES,
            'parent_category' => null,
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$expenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::EXPENSES.']...');
        }
        $this->info('Successfully Save '. AccountCategory::EXPENSES);


        $this->info('Category: '. AccountCategory::COST_OF_GOOD_SOLD);
        $costOfGoodSoldCategory = $this->saveCategory([
            'name' => AccountCategory::COST_OF_GOOD_SOLD,
            'parent_category' => $expenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$costOfGoodSoldCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::COST_OF_GOOD_SOLD.']...');
        }
        $this->info('Successfully Save '. AccountCategory::COST_OF_GOOD_SOLD);


        $this->info('Category: '. AccountCategory::DIRECT_EXPENSE);
        $directExpenseCategory = $this->saveCategory([
            'name' => AccountCategory::DIRECT_EXPENSE,
            'parent_category' => $expenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$directExpenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::DIRECT_EXPENSE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::DIRECT_EXPENSE);

        $this->info('Category: '. AccountCategory::IN_DIRECT_EXPENSE);
        $indirectExpenseCategory = $this->saveCategory([
            'name' => AccountCategory::IN_DIRECT_EXPENSE,
            'parent_category' => $expenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$indirectExpenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::IN_DIRECT_EXPENSE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::IN_DIRECT_EXPENSE);


        $this->info('Category: '. AccountCategory::SALARY_EXPENSE);
        $salaryExpenseCategory = $this->saveCategory([
            'name' => AccountCategory::SALARY_EXPENSE,
            'parent_category' => $directExpenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$salaryExpenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::SALARY_EXPENSE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::SALARY_EXPENSE);


        $this->info('Category: '. AccountCategory::OPERATING_EXPENSE);
        $operatingExpenseCategory = $this->saveCategory([
            'name' => AccountCategory::OPERATING_EXPENSE,
            'parent_category' => $expenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$operatingExpenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::OPERATING_EXPENSE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::OPERATING_EXPENSE);


        $this->info('Category: '. AccountCategory::INTEREST_EXPENSE);
        $interestExpenseCategory = $this->saveCategory([
            'name' => AccountCategory::INTEREST_EXPENSE,
            'parent_category' => $expenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$interestExpenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::INTEREST_EXPENSE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::INTEREST_EXPENSE);


        $this->info('Category: '. AccountCategory::TAX);
        $taxExpenseCategory = $this->saveCategory([
            'name' => AccountCategory::TAX,
            'parent_category' => $expenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$taxExpenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::TAX.']...');
        }
        $this->info('Successfully Save '. AccountCategory::TAX);

        $this->info('Category: '. AccountCategory::EVAPORATIVE);
        $evaporativeExpenseCategory = $this->saveCategory([
            'name' => AccountCategory::EVAPORATIVE,
            'parent_category' => $expenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$evaporativeExpenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::EVAPORATIVE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::EVAPORATIVE);


        $this->info('Category: '. AccountCategory::DRIVER_SALE);
        $driverSaleExpenseCategory = $this->saveCategory([
            'name' => AccountCategory::DRIVER_SALE,
            'parent_category' => $expenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$driverSaleExpenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::DRIVER_SALE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::DRIVER_SALE);

        $this->info('Category: '. AccountCategory::DRIVER_SALE);
        $driverSaleExpenseCategory = $this->saveCategory([
            'name' => AccountCategory::DRIVER_SALE,
            'parent_category' => $expenseCategory['id'],
            'type' => FuelMatixCategoryType::EXPENSE,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$driverSaleExpenseCategory instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::DRIVER_SALE.']...');
        }
        $this->info('Successfully Save '. AccountCategory::DRIVER_SALE);


        $this->info('Category: '. AccountCategory::RETAIN_EARNING);
        $retainEarning = $this->saveCategory([
            'name' => AccountCategory::RETAIN_EARNING,
            'parent_category' => $equityCategory['id'],
            'type' => FuelMatixCategoryType::EQUITY,
            'client_company_id' => $clientCompany->id,
        ]);
        if (!$retainEarning instanceof Category) {
            $this->warn('Cannot save category ['.AccountCategory::RETAIN_EARNING.']...');
        }
        $this->info('Successfully Save '. AccountCategory::RETAIN_EARNING);

        print_r('Successfully save category.'. PHP_EOL);
    }
}
