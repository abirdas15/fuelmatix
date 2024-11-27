<?php

namespace App\Console\Commands;

use App\Models\ClientCompany;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;

class AdminPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give Admin Permission';

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
        $roles = Role::get()->toArray();
        $permission = Permission::getAllPermission();
        $permissionData = [];
        foreach ($roles as $role) {
            $this->info('Permission For: ' . $role['name']);
            Permission::where('role_id', $role['id'])->delete();
            foreach ($permission as $permissionName) {
                $permissionData[] = [
                    'name' => $permissionName,
                    'role_id' => $role['id'],
                    'client_company_id' => $role['client_company_id']
                ];
            }
        }
        Permission::insert($permissionData);
        print_r("Successfully give permission.".PHP_EOL);
    }
}
