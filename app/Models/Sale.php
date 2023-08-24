<?php

namespace App\Models;

use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $table = 'sale';
    public $timestamps = false;
    protected $hidden = [
        'user_id',
        'client_company_id',
        'note'
    ];
    public static function getInvoiceNumber(): string
    {
        $sessionUser = SessionUser::getUser();
        $latest = Sale::where('client_company_id', $sessionUser['client_company_id'])->orderBy('id', 'DESC')->first();
        $number = 1;
        if ($latest instanceof Sale) {
            $invoiceNumber = explode('-', $latest['invoice_number']);
            if (!empty($invoiceNumber[1])) {
                $number = $number + $invoiceNumber[1];
            }
        }
        $date = Carbon::now();
        return Helpers::formatDate($date, 'Ymdhi').'-'.$number;
    }
}
