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
               'parent_category' => 0,
               'balance' => 0,
                'type' => 'assets',
                'category_hericy' => json_encode(['Assets'])
            ],
            [
                'category' => 'Equity',
                'parent_category' => 0,
                'type' => 'equity',
                'balance' => 0,
                'category_hericy' => json_encode(['Equity'])
            ],
            [
                'category' => 'Liabilities',
                'parent_category' => 0,
                'type' => 'liabilities',
                'balance' => 0,
                'category_hericy' => json_encode(['Liabilities'])
            ],
            [
                'category' => 'Income',
                'parent_category' => 0,
                'type' => 'income',
                'balance' => 0,
                'category_hericy' => json_encode(['Income'])
            ],
            [
                'category' => 'Expenses',
                'parent_category' => 0,
                'type' => 'expenses',
                'balance' => 0,
                'category_hericy' => json_encode(['Expenses'])
            ]
        ];
        Category::truncate();
        Category::insert($data);
        print_r('Successfully create category.'. PHP_EOL);
    }
}
