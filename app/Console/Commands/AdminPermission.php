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
    protected $signature = 'admin:permission';

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
        $this->info('Enter Company ID');
        $companyId = $this->ask('Company Id:');
        if (empty($companyId)) {
            $this->warn('Company cannot be blank.');
            return;
        }
        $company = ClientCompany::where('id', $companyId)->first();
        if (!$company instanceof ClientCompany) {
            $this->warn('Cannot found company.');
            return;
        }
        $roles = Role::get()->toArray();
        $permission = Permission::getAllPermission();
        $permissionData = [];
        foreach ($roles as $role) {
            Permission::where('role_id', $role['id'])->delete();
            foreach ($permission as $permissionName) {
                $permissionData[] = [
                    'name' => $permissionName,
                    'role_id' => $role['id'],
                    'client_company_id' => $companyId
                ];
            }
        }
        Permission::insert($permissionData);
        print_r("Successfully give permission.".PHP_EOL);
    }
}
