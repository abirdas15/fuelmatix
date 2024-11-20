<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
}
