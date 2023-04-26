<?php

namespace App\Console\Commands;

use App\Models\Category;
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
        $data = [
            [
               'category' => 'Assets',
               'parent_category' => null,
               'balance' => 0,
                'type' => 'assets',
                'category_hericy' => json_encode(['Assets'])
            ],
            [
                'category' => 'Equity',
                'parent_category' => null,
                'type' => 'equity',
                'balance' => 0,
                'category_hericy' => json_encode(['Equity'])
            ],
            [
                'category' => 'Liabilities',
                'parent_category' => null,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities'])
            ],
            [
                'category' => 'Income',
                'parent_category' => null,
                'type' => 'income',
                'balance' => 0,
                'category_hericy' => json_encode(['Income'])
            ],
            [
                'category' => 'Expenses',
                'parent_category' => null,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses'])
            ],
            [
                'category' => 'Current Assets',
                'parent_category' => 1,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(['Assets', 'Current Assets'])
            ],
            [
                'category' => 'Account Receivable',
                'parent_category' => 6,
                'type' => 'assets',
                'balance' => 0,
                'category_hericy' => json_encode(['Assets', 'Current Asset', 'Account Receivable'])
            ],
            [
                'category' => 'Current Liabilities',
                'parent_category' => 3,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities', 'Current Liabilities'])
            ],
            [
                'category' => 'Account Payable',
                'parent_category' => 8,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities', 'Current Liabilities', 'Account Payable'])
            ],
        ];
        Category::truncate();
        Category::insert($data);
        print_r('Successfully create category.'. PHP_EOL);
    }
}
