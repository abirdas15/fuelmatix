<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function list(Request $request)
    {
        $result = Category::select('id', 'category', 'balance', 'parent_category', 'description')
            ->with(['children' => function($q) {
                $q->select('id', 'category', 'parent_category', 'balance', 'description');
            }])
            ->whereNull('parent_category')
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function parent(Request $request)
    {
        $result = Category::select('id', 'category_hericy', 'type')
            ->get()
            ->toArray();
        foreach ($result as &$data) {
            $category = json_decode($data['category_hericy']);
            $data['category'] = implode(':', $category);
            unset($data['category_hericy']);
        }
        usort($result, function ($item1, $item2) {
            return $item1['category'] <=> $item2['category'];
        });

        return response()->json(['status' => 200, 'data' => $result]);
    }
}
