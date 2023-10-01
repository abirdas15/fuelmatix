<?php

namespace App\Console\Commands;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Models\Category;
use App\Models\ClientCompany;
use Illuminate\Console\Command;

class AccountHead extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Account:Head';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $clientCompany = ClientCompany::first();
        $data = [
            [
               'category' => AccountCategory::ASSETS,
               'parent_category' => null,
               'balance' => 0,
                'type' => 'assets',
                'category_hericy' => json_encode([AccountCategory::ASSETS]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1])
            ],
            [
                'category' => AccountCategory::EQUITY,
                'parent_category' => null,
                'type' => 'equity',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::EQUITY]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([2])
            ],
            [
                'category' => AccountCategory::LIABILITIES,
                'parent_category' => null,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::LIABILITIES]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([3])
            ],
            [
                'category' => AccountCategory::INCOME,
                'parent_category' => null,
                'type' => 'income',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::INCOME]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([4])
            ],
            [
                'category' => AccountCategory::EXPENSES,
                'parent_category' => null,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::EXPENSES]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5])
            ],
            [
                'category' => AccountCategory::CURRENT_ASSETS,
                'parent_category' => 1,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::ASSETS, AccountCategory::CURRENT_ASSETS]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6])
            ],
            [
                'category' => AccountCategory::ACCOUNT_RECEIVABLE,
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::ASSETS, AccountCategory::CURRENT_ASSETS, AccountCategory::ACCOUNT_RECEIVABLE]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,7])
            ],
            [
                'category' => AccountCategory::CURRENT_LIABILITIES,
                'parent_category' => 3,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::LIABILITIES, AccountCategory::CURRENT_LIABILITIES]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([3,8])
            ],
            [
                'category' => AccountCategory::ACCOUNT_PAYABLE,
                'parent_category' => 8,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::LIABILITIES, AccountCategory::CURRENT_LIABILITIES, AccountCategory::ACCOUNT_PAYABLE]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([3,8,9])
            ],
            [
                'category' => AccountCategory::STOCK_IN_HAND,
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::ASSETS, AccountCategory::CURRENT_ASSETS, AccountCategory::STOCK_IN_HAND]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,10])
            ],
            [
                'category' => AccountCategory::CASH_IM_HAND,
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::ASSETS, AccountCategory::CURRENT_ASSETS, AccountCategory::CASH_IM_HAND]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,11])
            ],
            [
                'category' => AccountCategory::CASH,
                'parent_category' => 11,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::ASSETS, AccountCategory::CURRENT_ASSETS, AccountCategory::CASH_IM_HAND, AccountCategory::CASH]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,11,12])
            ],
            [
                'category' => AccountCategory::BANK,
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::ASSETS, AccountCategory::CURRENT_ASSETS, AccountCategory::BANK]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,13])
            ],
            [
                'category' => AccountCategory::POS_MACHINE,
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::ASSETS, AccountCategory::CURRENT_ASSETS, AccountCategory::POS_MACHINE]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,14])
            ],
            [
                'category' => AccountCategory::COST_OF_GOOD_SOLD,
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::EXPENSES, AccountCategory::COST_OF_GOOD_SOLD]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,15])
            ],
            [
                'category' => AccountCategory::SALARY_EXPENSE,
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::EXPENSES, AccountCategory::SALARY_EXPENSE]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,16])
            ],
            [
                'category' => 'Operating Expense',
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::EXPENSES, AccountCategory::OPERATING_EXPENSE]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,17])
            ],
            [
                'category' => AccountCategory::INTEREST_EXPENSE,
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::EXPENSES, AccountCategory::INTEREST_EXPENSE]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,18])
            ],
            [
                'category' => 'Tax',
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::EXPENSES, AccountCategory::TAX]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,19])
            ],
            [
                'category' => AccountCategory::EVAPORATIVE,
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::EXPENSES, AccountCategory::EVAPORATIVE]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,20])
            ],
            [
                'category' => AccountCategory::DRIVER_SALE,
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::EXPENSES, AccountCategory::DRIVER_SALE]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,21])
            ],
            [
                'category' => AccountCategory::UN_EARNED_REVENUE,
                'parent_category' => 3,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode([AccountCategory::LIABILITIES, AccountCategory::UN_EARNED_REVENUE]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([3,8,22])
            ],
        ];
        Category::truncate();
        Category::insert($data);
        print_r('Successfully create category.'. PHP_EOL);
    }
}
