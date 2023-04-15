<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            $data['category'] = implode('-->', $category);
            unset($data['category_hericy']);
        }
        usort($result, function ($item1, $item2) {
            return $item1['category'] <=> $item2['category'];
        });

        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'category' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = new Category();
        $category->category = $inputData['category'];
        $category->code = $inputData['code'] ?? null;
        $category->parent_category = !empty($inputData['parent_category']) ? $inputData['parent_category'] : null;
        $category->type = $inputData['type'];
        $category->description = $inputData['description'] ?? null;
        if ($category->save()) {
            if (!empty($inputData['parent_category'])) {
                $parentCategory = Category::select('category_hericy')->where('id', $inputData['parent_category'])->first();
                $category_hericy = json_decode($parentCategory['category_hericy']);
                array_push($category_hericy, $category->category);
                $category_hericy = json_encode($category_hericy);
            } else {
                $category_hericy = json_encode([$category->category]);
            }
            $category->category_hericy = $category_hericy;
            $category->save();
            return response()->json(['status' => 200, 'msg' => 'Successfully save category']);
        }
        return response()->json(['status' => 200, 'msg' => 'Can not save category']);
    }
    public function single(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Category::select('id', 'category', 'code', 'parent_category', 'type')
            ->where('id', $inputData['id'])
            ->first();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'category' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        $category->category = $inputData['category'];
        $category->code = $inputData['code'] ?? null;
        $category->parent_category = !empty($inputData['parent_category']) ? $inputData['parent_category'] : null;
        $category->type = $inputData['type'];
        $category->description = $inputData['description'] ?? null;
        if ($category->save()) {
            if (!empty($inputData['parent_category'])) {
                $parentCategory = Category::select('category_hericy')->where('id', $inputData['parent_category'])->first();
                $category_hericy = json_decode($parentCategory['category_hericy']);
                array_push($category_hericy, $category->category);
                $category_hericy = json_encode($category_hericy);
            } else {
                $category_hericy = json_encode([$category->category]);
            }
            $category->category_hericy = $category_hericy;
            $category->save();
            return response()->json(['status' => 200, 'msg' => 'Successfully update category']);
        }
        return response()->json(['status' => 200, 'msg' => 'Can not update category']);
    }
}
