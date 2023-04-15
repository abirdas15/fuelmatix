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
    public function get(Request $request)
    {
        $result = Category::select('id', 'category', 'balance', 'parent_category', 'description')
            ->with(['children' => function($q) {
                $q->select('id', 'category', 'parent_category', 'balance', 'description');
            }])
            ->whereNull('parent_category')
            ->get()
            ->toArray();

        $dataArray = [];
        foreach ($result as $data) {
            return self::getChildCategory($data);
        }

        return $dataArray;
    }
    public static function getChildCategory($data)
    {
        $categoryArray = [];
        foreach ($data['children'] as $key => $category) {
            $categoryArray[$key][] = $data['category'];
            if ($data['id'] == $category['parent_category']) {
                $categoryArray[$key][] = $category['category'];
            }
//            if (count($category['children']) > 0) {
//                $categoryArray[$key][] = self::getChildCategory($category);
//            }
        }
        return $categoryArray;
    }
}
