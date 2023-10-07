<?php

namespace App\Console\Commands;

use App\Models\ClientCompany;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class AdminReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Admin:Reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $company = ClientCompany::first();
        $permission = Permission::getAllPermission();
        Role::truncate();
        User::truncate();
        Permission::truncate();

        $role = new Role();
        $role->name = 'Admin';
        $role->is_default = 1;
        $role->client_company_id = $company['id'];
        $role->save();

        $user = new User();
        $user->name = 'Admin';
        $user->password = bcrypt('12345678');
        $user->email = 'admin@gmail.com';
        $user->role_id = $role['id'];
        $user->client_company_id = $company['id'];
        $user->save();
        $permissionData = [];
        foreach ($permission as $permissionName) {
            $permissionData[] = [
                'name' => $permissionName,
                'role_id' => $role->id,
                'client_company_id' => $company['id']
            ];
        }
        Permission::insert($permissionData);
        print_r("Successfully generate admin.".PHP_EOL);
    }
}
