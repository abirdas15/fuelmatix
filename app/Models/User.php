<?php

namespace App\Models;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\TokenRepository;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @param User $userInfo
     * @return array
     */
    public static function ParseData(User $userInfo): array
    {
        $clientCompany = ClientCompany::find($userInfo->client_company_id);
        $permission = Permission::where('role_id', $userInfo['role_id'])->get()->pluck('name');
        return [
            'id' => $userInfo->id,
            'name' => $userInfo->name,
            'email' => $userInfo->email,
            'client_company_id' => $userInfo->client_company_id,
            'voucher_check' => $clientCompany['voucher_check'] ?? 0,
            'company_name' => $clientCompany->name ?? '',
            'permission' => $permission,
            'currency_precision' => $clientCompany->currency_precision,
            'quantity_precision' => $clientCompany->quantity_precision
        ];
    }
    public static function findUserByToken()
    {
        $bearerToken = request()->bearerToken();
        // If no bearer token is provided, return null
        if ($bearerToken == null) {
            return null;
        }

        // Decode the token. The token is assumed to be a JWT, which is base64url encoded and
        // consists of three parts separated by dots (header, payload, signature).
        // Extract and decode the payload (second part of the JWT).
        try {
            $tokens = json_decode(array_map(
                function ($v) {
                    return base64_decode($v);
                }, // Decode each part from base64
                explode('.', $bearerToken) // Split the token into parts
            )[1], true, 512, JSON_THROW_ON_ERROR); // Decode JSON payload

            // Instantiate the TokenRepository to find the token in the database
            $tokenRepository = new TokenRepository();

            // Find the token using the 'jti' (JWT ID) claim from the payload
            $passportToken = $tokenRepository->find($tokens['jti']);

            // If the token is not found in the repository, return null
            if (!$passportToken) {
                return null;
            }

            return User::where('id', $passportToken->user_id)->first();
        } catch (\Exception $exception) {
            return null;
        }
    }
    /**
     * @return Category
     */
    public function saveStaffLoanCategory(): Category
    {
        $sessionUser = SessionUser::getUser();
        $staffLoanReceivableCategory = Category::where('slug', strtolower(AccountCategory::STAFF_LOAN_RECEIVABLES))
            ->where('client_company_id', $sessionUser->client_company_id)
            ->first();
        if (!$staffLoanReceivableCategory instanceof Category) {
            $assetCategory = Category::where('slug', strtolower(AccountCategory::ASSETS))
                ->where('client_company_id', $sessionUser->client_company_id)
                ->first();
            $staffLoanReceivableCategory = new Category();
            $staffLoanReceivableCategory->name = AccountCategory::STAFF_LOAN_RECEIVABLES;
            $staffLoanReceivableCategory->slug = strtolower(AccountCategory::STAFF_LOAN_RECEIVABLES);
            $staffLoanReceivableCategory->parent_category = $assetCategory->id;
            $staffLoanReceivableCategory->type = $assetCategory->type;
            $staffLoanReceivableCategory->default = 1;
            $staffLoanReceivableCategory->client_company_id = $sessionUser['client_company_id'];
            $staffLoanReceivableCategory->save();
            $staffLoanReceivableCategory->updateCategory();
        }
        $staffLoanCategory = Category::where('module', Module::STAFF_LOAN_RECEIVABLE)
            ->where('module_id', $this['id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        if (!$staffLoanCategory instanceof Category) {
            $staffLoanCategory = new Category();
        }
        $staffLoanCategory->name = $this['name'];
        $staffLoanCategory->slug = strtolower($this['name']);
        $staffLoanCategory->parent_category = $staffLoanReceivableCategory->id;
        $staffLoanCategory->type = $staffLoanReceivableCategory->type;
        $staffLoanCategory->client_company_id = $sessionUser['client_company_id'];
        $staffLoanCategory->module = Module::STAFF_LOAN_RECEIVABLE;
        $staffLoanCategory->module_id = $this['id'];
        $staffLoanCategory->save();
        $staffLoanCategory->updateCategory();
        return $staffLoanCategory;
    }
}
