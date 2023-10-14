<?php

namespace App\Console\Commands;

use App\Models\ClientCompany;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:company';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create New Company';

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
        $this->info('Create New Company');
        $companyName = $this->ask('Company Name:');
        if (empty($companyName)) {
            $this->warn('Company name cannot be blank.');
            return;
        }
        $address = $this->ask('Company Address:');
        if (empty($address)) {
            $this->warn('Address cannot be blank.');
            return;
        }
        $companyEmail = $this->ask('Company Email:');
        $phone = $this->ask('Phone:');

        $this->info('Login Credential');
        $userEmail = $this->ask('User Email:');
        if (empty($userEmail)) {
            $this->warn('User email cannot be blank.');
            return;
        }
        $user = User::where('email', $userEmail)->first();
        if ($user instanceof User) {
            $this->warn('User email already exists.');
            return;
        }
        $userPassword = $this->ask('User Password:');
        if (empty($userPassword)) {
            $this->warn('User password cannot be blank.');
            return;
        }
        $clientCompanyModel = new ClientCompany();
        $clientCompanyModel->name = $companyName;
        $clientCompanyModel->address = $address;
        $clientCompanyModel->email = $companyEmail ?? null;
        $clientCompanyModel->phone_number = $phone ?? null;
        if (!$clientCompanyModel->save()) {
            $this->warn('Cannot save company.');
            return;
        }
        $this->info('Successfully save company.');

        $this->info('Create New Role');
        $role = new Role();
        $role->name = 'Admin';
        $role->is_default = 1;
        $role->client_company_id = $clientCompanyModel['id'];
        if (!$role->save()) {
            $this->warn('Cannot save Role.');
        }
        $this->info('Successfully create role.');

        $this->info('Create New User');
        $user = new User();
        $user->name = 'Admin';
        $user->password = bcrypt($userPassword);
        $user->email = $userEmail;
        $user->role_id = $role['id'];
        $user->client_company_id = $clientCompanyModel['id'];
        $user->save();
        $permissionData = [];
        $permission = Permission::getAllPermission();
        foreach ($permission as $permissionName) {
            $permissionData[] = [
                'name' => $permissionName,
                'role_id' => $role->id,
                'client_company_id' => $clientCompanyModel['id']
            ];
        }
        Permission::insert($permissionData);
        $this->info('Successfully Create New User.');

        $this->info('Create Account Category');
        Artisan::call('fuel:matix:category '.$clientCompanyModel['id']);
        $this->info('Successfully Create Account Category');
    }
}
