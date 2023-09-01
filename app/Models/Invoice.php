<?php

namespace App\Models;

use App\Helpers\SessionUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoices';
    public $timestamps = false;
    protected $hidden = [
        'client_company_id'
    ];

    public static function getInvoiceNumber()
    {
        $sessionUser = SessionUser::getUser();
        $invoice = Invoice::select('invoice_number')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->orderBy('id', 'DESC')
            ->first();
        if (!$invoice instanceof Invoice) {
            return 'INV-0001';
        }
        $string = preg_replace("/[^0-9\.]/", '', $invoice->invoice_number);
        return 'INV-' . sprintf('%04d', $string+1);
    }
}
