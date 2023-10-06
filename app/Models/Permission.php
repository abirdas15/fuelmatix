<?php

namespace App\Models;

use App\Common\Action;
use App\Common\Section;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permission';
    public $timestamps = false;

    /**
     * @return array
     */
    public static function getAllPermission(): array
    {
        $allBuildingPermissions = [];
        foreach (Section::getArray() as $section) {
            foreach (Action::getArray() as $userAction) {
                $allBuildingPermissions[] = self::getPermissionString($section, $userAction);
            }
        }

        return $allBuildingPermissions;
    }
    /**
     * @param string $sectionName
     * @param string $actionName
     * @return string
     */
    public static function getPermissionString(string $sectionName, string $actionName): string
    {
        $allowedSections = Section::getArray();
        $allowedUserActions = Action::getArray();
        if (in_array($sectionName, $allowedSections) && in_array($actionName, $allowedUserActions)) {
            return $sectionName . '-' . $actionName;
        }

        return '';
    }
}
