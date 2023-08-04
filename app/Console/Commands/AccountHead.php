<?php

namespace App\Console\Commands;

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
               'category' => 'Assets',
               'parent_category' => null,
               'balance' => 0,
                'type' => 'assets',
                'category_hericy' => json_encode(['Assets']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1])
            ],
            [
                'category' => 'Equity',
                'parent_category' => null,
                'type' => 'equity',
                'balance' => 0,
                'category_hericy' => json_encode(['Equity']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([2])
            ],
            [
                'category' => 'Liabilities',
                'parent_category' => null,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([3])
            ],
            [
                'category' => 'Income',
                'parent_category' => null,
                'type' => 'income',
                'balance' => 0,
                'category_hericy' => json_encode(['Income']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([4])
            ],
            [
                'category' => 'Expenses',
                'parent_category' => null,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5])
            ],
            [
                'category' => 'Current Assets',
                'parent_category' => 1,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(['Assets', 'Current Assets']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6])
            ],
            [
                'category' => 'Account Receivable',
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(['Assets', 'Current Asset', 'Account Receivable']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,7])
            ],
            [
                'category' => 'Current Liabilities',
                'parent_category' => 3,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities', 'Current Liabilities']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([3,8])
            ],
            [
                'category' => 'Account Payable',
                'parent_category' => 8,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities', 'Current Liabilities', 'Account Payable']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([3,8,9])
            ],
            [
                'category' => 'Stock In Hand',
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(["Assets", "Current Asset", "Stock In Hand"]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,10])
            ],
            [
                'category' => 'Cash In Hand',
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(["Assets", "Current Asset", "Cash In Hand"]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,11])
            ],
            [
                'category' => 'Cash',
                'parent_category' => 11,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(["Assets", "Current Asset", "Cash In Hand", "Cash"]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,11,12])
            ],
            [
                'category' => 'Bank',
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(["Assets", "Current Asset", "Bank"]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,13])
            ],
            [
                'category' => 'POS Machine',
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(["Assets", "Current Asset", "POS Machine"]),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([1,6,14])
            ],
            [
                'category' => 'Cost of Good Sold',
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses', 'Cost of Good Sold']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,15])
            ],
            [
                'category' => 'Salary Expense',
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses', 'Salary Expense']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,16])
            ],
            [
                'category' => 'Operating Expense',
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses', 'Operating Expense']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,17])
            ],
            [
                'category' => 'Interest Expense',
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses', 'Interest Expense']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,18])
            ],
            [
                'category' => 'Tax',
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses', 'Tax']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,19])
            ],
            [
                'category' => 'Evaporative',
                'parent_category' => 5,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses', 'Evaporative']),
                'default' => 1,
                'client_company_id' => $clientCompany->id,
                'category_ids' => json_encode([5,20])
            ],
        ];
        Category::truncate();
        Category::insert($data);
        print_r('Successfully create category.'. PHP_EOL);
    }
}
