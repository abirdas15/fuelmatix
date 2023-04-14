<?php

namespace App\Console\Commands;

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
               'name' => 'Assets',
               'parent_id' => 0,
               'inc' => 'dr',
               'dec' => 'cr',
               'balance' => 0
            ],
            [
                'name' => 'Equity',
                'parent_id' => 0,
                'inc' => 'dr',
                'dec' => 'cr',
                'balance' => 0
            ],
            [
                'name' => 'Liabilities',
                'parent_id' => 0,
                'inc' => 'dr',
                'dec' => 'cr',
                'balance' => 0
            ],
            [
                'name' => 'Income',
                'parent_id' => 0,
                'inc' => 'dr',
                'dec' => 'cr',
                'balance' => 0
            ],
            [
                'name' => 'Expenses',
                'parent_id' => 0,
                'inc' => 'dr',
                'dec' => 'cr',
                'balance' => 0
            ]
        ];
        \App\Models\AccountHead::truncate();
        \App\Models\AccountHead::insert($data);
        print_r('Successfully create account head.'. PHP_EOL);
    }
}
