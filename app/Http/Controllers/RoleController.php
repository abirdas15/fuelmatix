<?php

namespace App\Http\Controllers;

use App\Common\AccountBookAction;
use App\Common\AccountBookSection;
use App\Common\Action;
use App\Common\Section;
use App\Helpers\SessionUser;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $limit = $requestData['limit'] ?? 10;
        $sessionUser = SessionUser::getUser();
        $result = Role::select('roles.id', 'roles.name', 'roles.is_default')
            ->where('roles.client_company_id', $sessionUser['client_company_id'])
            ->paginate($limit);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
       $requestData = $request->all();
       $validator = Validator::make($requestData, [
           'name' => 'required|string',
           'permission' => 'array'
       ]);
       if ($validator->fails()) {
           return response()->json(['status' => 500, 'errors' => $validator->errors()]);
       }
       $sessionUser = SessionUser::getUser();
       $roleModel = new Role();
       $roleModel->name = $requestData['name'];
       $roleModel->client_company_id = $sessionUser['client_company_id'];
       if (!$roleModel->save()) {
           return response()->json(['status' => 500, 'message' => 'Cannot save role.']);
       }
       if (!empty($requestData['permission'])) {
           $permissionData = [];
           foreach ($requestData['permission'] as $permissionName) {
               $permissionData[] = [
                   'name' => $permissionName,
                   'role_id' => $roleModel['id'],
                   'client_company_id' => $sessionUser['client_company_id']
               ];
           }
           Permission::insert($permissionData);
       }
        return response()->json(['status' => 200, 'message' => 'Successfully saved permission.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        // Retrieve basic role information
        $result = Role::select('id', 'name')->where('id', $request['id'])->first();

        // Retrieve permissions associated with the role
        $permissions = Permission::select('name')->where('role_id', $request['id'])->get()->pluck('name')->toArray();

        // Prepare arrays for sections and actions with initial values
        $action = Action::getArray();
        $actionChecked = [];
        foreach ($action as $name) {
            $actionChecked[] = [
                'name' => ucfirst($name),
                'value' => $name,
                'checked' => false,
            ];
        }

        // Prepare arrays for sections with actions and initial values
        $section = Section::getArray();
        $sectionArray = [];
        foreach ($section as $name) {
            $sectionArray[] = [
                'name' => str_replace('-', ' ', ucfirst($name)),
                'value' => $name,
                'actions' => $actionChecked
            ];
        }

        // Mark actions as checked if they are in the permissions array
        foreach ($sectionArray as &$section) {
            foreach ($section['actions'] as &$action) {
                $sectionName = $section['value'] . '-' . $action['value'];
                if (in_array($sectionName, $permissions)) {
                    $action['checked'] = true;
                }
            }
        }

        // Attach sections with actions to the role information
        $result['sections'] = $sectionArray;

        // Return the complete detailed role information
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
            'permission' => 'array',
            'permission.*' => 'string'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $role = Role::where('name', $requestData['name'])->where('id', '!=', $requestData['id'])->first();
        if ($role instanceof Role) {
            return response()->json(['status' => 500, 'errors' => ['name' => ['The name has already been taken.']]]);
        }
        $role = Role::where('id', $requestData['id'])->first();
        if (!$role instanceof Role) {
            return response()->json(['status' => 400, 'message' => 'Cannot find role.']);
        }
        $role->name = $requestData['name'];
        if (!$role->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot updated role.']);
        }
        Permission::where('role_id', $requestData['id'])->delete();
        $sessionUser = SessionUser::getUser();
        if (!empty($requestData['permission'])) {
            $permissionData = [];
            foreach ($requestData['permission'] as $permissionName) {
                $permissionData[] = [
                    'name' => $permissionName,
                    'role_id' => $role['id'],
                    'client_company_id' => $sessionUser['client_company_id']
                ];
            }
            Permission::insert($permissionData);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated role.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $role = Role::where('id', $requestData['id'])->where('is_default', 1)->first();
        if ($role instanceof Role) {
            return response()->json(['status' => 400, 'message' => 'Cannot delete '.$role['name'].' role.']);
        }
        $user = User::where('role_id', $requestData['id'])->first();
        if ($user instanceof User) {
            return response()->json(['status' => 400, 'message' => 'Cannot delete role. Please delete user first.']);
        }
        Role::where('id', $requestData['id'])->delete();
        Permission::where('role_id', $requestData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted role.']);
    }
}
