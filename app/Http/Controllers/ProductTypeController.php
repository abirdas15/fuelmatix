<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{
    public function list(Request $request)
    {
        $result = ProductType::select('id', 'name')
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
