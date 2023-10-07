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
        $action = Action::getArray();
        $actionArray = [];
        foreach ($action as $name) {
            $actionArray[] = [
                'name' => ucfirst($name),
                'value' => $name
            ];
        }
        $section = Section::getArray();
        $sectionArray = [];
        foreach ($section as $name) {
            $sectionArray[] = [
                'name' => str_replace('-', ' ', ucfirst($name)),
                'value' => $name
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
