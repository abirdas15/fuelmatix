<?php

namespace App\Http\Controllers;

use App\Models\AccountHead;
use Illuminate\Http\Request;

class AccountHeadController extends Controller
{
    public function list(Request $request)
    {
        $result = AccountHead::select('id', 'name', 'balance', 'parent_id')
            ->with(['children' => function($q) {
                $q->select('id', 'name', 'parent_id', 'balance');
            }])
            ->where('parent_id', 0)
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
