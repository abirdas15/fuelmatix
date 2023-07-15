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
                'slug' => 'assets',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Equity',
                'parent_category' => null,
                'type' => 'equity',
                'balance' => 0,
                'category_hericy' => json_encode(['Equity']),
                'default' => 1,
                'slug' => 'equity',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Liabilities',
                'parent_category' => null,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities']),
                'default' => 1,
                'slug' => 'liabilities',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Income',
                'parent_category' => null,
                'type' => 'income',
                'balance' => 0,
                'category_hericy' => json_encode(['Income']),
                'default' => 1,
                'slug' => 'income',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Expenses',
                'parent_category' => null,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses']),
                'default' => 1,
                'slug' => 'expense',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Current Assets',
                'parent_category' => 1,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(['Assets', 'Current Assets']),
                'default' => 1,
                'slug' => 'current-asset',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Account Receivable',
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(['Assets', 'Current Asset', 'Account Receivable']),
                'default' => 1,
                'slug' => 'account-receivable',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Current Liabilities',
                'parent_category' => 3,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities', 'Current Liabilities']),
                'default' => 1,
                'slug' => 'current-liabilities',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Account Payable',
                'parent_category' => 8,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities', 'Current Liabilities', 'Account Payable']),
                'default' => 1,
                'slug' => 'account-payable',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Cash',
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(["Assets", "Current Asset", "Cash"]),
                'default' => 1,
                'slug' => 'cash',
                'client_company_id' => $clientCompany->id,
            ],
            [
                'category' => 'Bank',
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(["Assets", "Current Asset", "Bank"]),
                'default' => 1,
                'slug' => 'bank',
                'client_company_id' => $clientCompany->id,
            ],
        ];
        Category::truncate();
        Category::insert($data);
        print_r('Successfully create category.'. PHP_EOL);
    }
}
