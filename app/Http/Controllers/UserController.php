<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $user = new User();
        $user->name = $requestData['name'];
        $user->email = $requestData['email'];
        $user->password = bcrypt($requestData['password']);
        $user->phone = $requestData['phone'] ?? null;
        $user->address = $requestData['address'] ?? null;
        $user->client_company_id = $sessionUser['client_company_id'];
        if ($user->save()) {
            if (!empty($requestData['cashier_balance'])) {
                $cashInHandCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('category', AccountCategory::CASH_IM_HAND)->first();
                $category = new Category();
                $category->category = $requestData['name'];
                $category->parent_category = $cashInHandCategory->id;
                $category->type = $cashInHandCategory->type;
                $category->client_company_id = $sessionUser['client_company_id'];
                if ($category->save()) {
                    $category->updateCategory();
                    $user->category_id = $category->id;
                    $user->save();
                    return response()->json(['status' => 200, 'message' => 'Successfully saved user.']);
                }
            }
        }
        return response()->json(['status' => 500, 'message' => 'Cannot saved user.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $sessionUser = SessionUser::getUser();
        $limit = $requestData['limit'] ?? 10;
        $orderBy = $requestData['order_by'] ?? 'id';
        $orderMode = $requestData['order_mode'] ?? 'DESC';
        $keyword = $requestData['keyword'] ?? '';
        $result = User::select('id', 'name', 'email', 'phone', 'category_id', 'address')
            ->where('client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', '%'.$keyword.'%');
                $q->owWhere('email', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = User::select('id', 'name', 'email', 'phone', 'category_id', 'address')
            ->where('id', $requestData['id'])
            ->first();
        return response()->json(['status' => 200, 'data' => $result]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'sometimes|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $user = User::where('client_company_id', $sessionUser['client_company_id'])->where('email', $requestData['email'])->where('id', '!=', $requestData['id'])->first();
        if ($user instanceof User) {
            return response()->json(['status' => 500, 'errors' => ['email' => ['The email already have been taken.']]]);
        }

        $user = User::find($requestData['id']);
        if (!$user instanceof User) {
            return response()->json(['status' => 500, 'message' => 'Cannot find user']);
        }
        $user->name = $requestData['name'];
        $user->email = $requestData['email'];
        if (!empty($requestData['password'])) {
            $user->password = bcrypt($requestData['password']);
        }
        $user->phone = $requestData['phone'] ?? null;
        $user->address = $requestData['address'] ?? null;
        $user->client_company_id = $sessionUser['client_company_id'];
        if ($user->save()) {
            if (!empty($requestData['cashier_balance']) && empty($user['category_id'])) {
                $cashInHandCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('category', AccountCategory::CASH_IM_HAND)->first();
                $category = new Category();
                $category->category = $requestData['name'];
                $category->parent_category = $cashInHandCategory->id;
                $category->type = $cashInHandCategory->type;
                $category->client_company_id = $sessionUser['client_company_id'];
                if ($category->save()) {
                    $category->updateCategory();
                    $user->category_id = $category->id;
                    $user->save();
                    return response()->json(['status' => 200, 'message' => 'Successfully updated user.']);
                }
            } else if (!empty($requestData['cashier_balance']) && !empty($user['category_id'])) {
                $category = Category::find($user->category_id);
                $category->category = $requestData['name'];
                if ($category->save()) {
                    $category->updateCategory();
                    $user->category_id = $category->id;
                    $user->save();
                    return response()->json(['status' => 200, 'message' => 'Successfully updated user.']);
                }
            } else if (empty($requestData['cashier_balance'])) {
                $user->category_id = null;
                $user->save();
            }
        }
        return response()->json(['status' => 500, 'message' => 'Cannot updated user.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $user = User::find($requestData['id']);
        if (!$user instanceof User) {
            return response()->json(['status' => 500, 'message' => 'Cannot find user.']);
        }
        if (!empty($user['category_id'])) {
            $transaction = Transaction::where('account_id', $user['category_id'])->orWhere('linked_id', $user['category_id'])->first();
            if ($transaction instanceof Transaction) {
                return response()->json(['status' => 500, 'message' => 'Cannot deleted user. Because user already have been transaction.']);
            }
        }
        User::where('id', $requestData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted user.']);
    }
}
