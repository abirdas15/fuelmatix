<?php

namespace App\Http\Controllers;

use App\Common\Action;
use App\Common\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllPermission(Request $request): JsonResponse
    {
        // Get default actions from AccountBookAction
        $actions = Action::getArray();
        $actionArray = [];
        $actionChecked = [];

        // Generate action arrays with initial checked state
        foreach ($actions as $name) {
            $actionArray[] = [
                'name' => ucfirst($name),
                'value' => $name,
                'checked' => false
            ];
            $actionChecked[] = [
                'name' => ucfirst($name),
                'value' => $name,
                'checked' => false,
            ];
        }

        // Get default sections from AccountBookSection
        $sections = Section::getArray();
        $sectionArray = [];

        // Generate section arrays with associated actions
        foreach ($sections as $name) {
            $sectionArray[] = [
                'name' => str_replace('-', ' ', ucfirst($name)),
                'value' => $name,
                'actions' => $actionChecked
            ];
        }
        return response()->json([
            'status' => 200,
            'data' => [
                'actions' => $actionArray,
                'sections' => $sectionArray
            ]
        ]);
    }
}
