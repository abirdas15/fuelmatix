<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Repository\CategoryRepository;
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
            'name' => 'required|string',
            'role_id' => 'required|integer',
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
        $user->role_id = $requestData['role_id'];
        $user->password = bcrypt($requestData['password']);
        $user->phone = $requestData['phone'] ?? null;
        $user->address = $requestData['address'] ?? null;
        $user->client_company_id = $sessionUser['client_company_id'];
        $user->cashier_balance = !empty($requestData['cashier_balance']) ? 1 : 0;
        if (!$user->save()) {
            return response()->json(['status' => 500, 'message' => 'Cannot saved [user].']);
        }
        if (!empty($requestData['cashier_balance'])) {
            $cashInHandCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::CASH_IM_HAND))->first();
            if (!$cashInHandCategory instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [cash in hand] category']);
            }
            $categoryData = [
                'name' => $requestData['name'],
            ];
            $cashInHandCategory = CategoryRepository::saveCategory($categoryData, $cashInHandCategory['id'], null);
            if ($cashInHandCategory instanceof Category) {
                $user->category_id = $cashInHandCategory->id;
                $user->save();
            }
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved user.']);
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
        $result = User::select('users.id', 'users.name', 'users.email', 'users.phone', 'users.category_id', 'users.address', 'roles.name as role')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('users.name', 'LIKE', '%'.$keyword.'%');
                $q->owWhere('users.email', 'LIKE', '%'.$keyword.'%');
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
    public function single(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = User::select('id', 'name', 'email', 'phone', 'address', 'cashier_balance', 'role_id')
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
            'id' => 'required|integer',
            'name' => 'required|string',
            'role_id' => 'required|integer',
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
        $user->role_id = $requestData['role_id'];
        $user->email = $requestData['email'];
        if (!empty($requestData['password'])) {
            $user->password = bcrypt($requestData['password']);
        }
        $user->phone = $requestData['phone'] ?? null;
        $user->address = $requestData['address'] ?? null;
        $user->client_company_id = $sessionUser['client_company_id'];
        $user->cashier_balance = !empty($requestData['cashier_balance']) ? 1 : 0;
        if (!$user->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot updated [user].']);
        }
        $categoryData = [
            'name' => $requestData['name'],
        ];
        if (!empty($requestData['cashier_balance']) && empty($user['category_id'])) {
            $cashInHandCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::CASH_IM_HAND))->first();
            if (!$cashInHandCategory instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [cash in hand] category']);
            }
            $cashInHandCategory = CategoryRepository::saveCategory($categoryData, $cashInHandCategory['id'], null);
            if ($cashInHandCategory instanceof Category) {
                $user->category_id = $cashInHandCategory->id;
                $user->save();
            }
        } else if (!empty($requestData['cashier_balance']) && !empty($user['category_id'])) {
            $category = Category::find($user->category_id);
            if (!$category instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [cash in hand] category']);
            }
            CategoryRepository::updateCategory($category, $categoryData);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated user.']);
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
