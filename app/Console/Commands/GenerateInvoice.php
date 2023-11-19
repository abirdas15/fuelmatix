<?php

namespace App\Console\Commands;

use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SaleData;
use App\Models\ShiftSale;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Invoice';

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
        $startDate = date('Y-m-d', strtotime('first day of last month'));
        $endDate = date('Y-m-t', strtotime($startDate));
        $transaction = Transaction::select('id','module', 'module_id', 'description', 'linked_id as category_id', 'debit_amount as amount')
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->toArray();
        $transactionArray = [];
        foreach ($transaction as $data) {
            $transactionArray[$data['category_id']][] = $data;
        }
        foreach ($transactionArray as $key =>  $data) {
            $invoiceItem = [];
            foreach ($data as $row) {
                if ($row['module'] == Module::POS_SALE) {
                    $posSale = SaleData::select('sale_data.product_id', 'sale_data.quantity', 'sale_data.price', 'sale_data.subtotal', 'sale.date')
                        ->leftJoin('sale', 'sale.id', 'sale_data.sale_id')
                        ->where('sale_data.sale_id', $row['module_id'])
                        ->get()
                        ->toArray();
                    foreach ($posSale as &$sale) {
                        $sale['transaction_id'] = $row['id'];
                        $sale['description'] = $row['description'] ?? null;
                        $invoiceItem[] = $sale;
                    }
                } else if ($row['module'] == Module::SHIFT_SALE) {
                    $shiftSale = ShiftSale::select('shift_sale.product_id', 'products.selling_price as price', 'shift_sale.date')
                        ->leftJoin('products', 'products.id', '=', 'shift_sale.product_id')
                        ->where('shift_sale.id', $row['module_id'])
                        ->first();
                    if ($shiftSale instanceof ShiftSale) {
                        $shiftSale['quantity'] = $row['amount'] / $shiftSale['price'];
                        $shiftSale['subtotal'] = $row['amount'];
                        $shiftSale['transaction_id'] = $row['id'];
                        $shiftSale = $shiftSale->toArray();
                        $invoiceItem[] = $shiftSale;
                    }
                }
            }
            dd($invoiceItem);
            $invoice = new Invoice();
            $invoice->invoice_number = Invoice::getInvoiceNumber();
            $invoice->date = Carbon::now();
            $invoice->category_id = $key;
            $invoice->amount = array_sum(array_column($invoiceItem, 'subtotal'));
            $invoice->status = 'due';
            $invoice->due_date = Carbon::now()->add('30D');
            $invoice->client_company_id = $sessionUser['client_company_id'];
            if ($invoice->save()) {
                foreach ($invoiceItem as $item) {
                    $invoiceItemObj = new InvoiceItem();
                    $invoiceItemObj->invoice_id = $invoice->id;
                    $invoiceItemObj->transaction_id = $item['transaction_id'];
                    $invoiceItemObj->car_number = $item['description'] ?? null;
                    $invoiceItemObj->product_id = $item['product_id'];
                    $invoiceItemObj->quantity = $item['quantity'];
                    $invoiceItemObj->price = $item['price'];
                    $invoiceItemObj->subtotal = $item['subtotal'];
                    $invoiceItemObj->date = Carbon::parse($item['date'])->format('Y-m-d');
                    $invoiceItemObj->save();
                }
            }
        }
    }
}
