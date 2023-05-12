<?php

namespace App\Http\Controllers;

use App\Models\ShiftSale;
use App\Models\ShiftSummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftSaleController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        if (isset($inputData['shift_sale'])) {
            if ($inputData['shift_sale']['status'] == 'start') {
                $shiftSale = new ShiftSale();
                $shiftSale->product_id = $inputData['shift_sale']['product_id'];
                $shiftSale->start_date = Carbon::now();
                $shiftSale->end_date = null;
            } else if ($inputData['shift_sale']['status'] == 'end') {
                $shiftSale = ShiftSale::find($inputData['shift_sale']['id']);
                $shiftSale->end_date = Carbon::now();
            }
            $shiftSale->start_reading = $inputData['shift_sale']['start_reading'];
            $shiftSale->end_reading = $inputData['shift_sale']['end_reading'];
            $shiftSale->consumption = $inputData['shift_sale']['consumption'];
            $shiftSale->amount = $inputData['shift_sale']['amount'];
            $shiftSale->status = $inputData['shift_sale']['status'];
            $shiftSale->user_id = Auth::user()->id;
            if ($shiftSale->save()) {
                if (isset($inputData['summary']) && count($inputData['summary']) > 0) {
                    foreach ($inputData['summary'] as $summary) {
                        foreach ($summary['nozzle'] as $nozzle) {
                            ShiftSummary::updateOrCreate([
                                'shift_sale_id' => $shiftSale->id,
                                'dispenser_id' => $summary['id'],
                                'nozzle_id' => $nozzle['id']
                            ],[
                                'shift_sale_id' => $shiftSale->id,
                                'dispenser_id' => $summary['id'],
                                'nozzle_id' => $nozzle['id'],
                                'start_reading' => $nozzle['start_reading'],
                                'end_reading' => $nozzle['end_reading'],
                                'consumption' => $nozzle['consumption'],
                                'amount' => $nozzle['amount'],
                            ]);
                        }
                    }
                }
                return response()->json(['status' => 200, 'message' => 'Successfully save shift sale.']);
            }
        }
        return response()->json(['status' => 500, 'error' => 'Cannot save shift sale.']);
    }
}
