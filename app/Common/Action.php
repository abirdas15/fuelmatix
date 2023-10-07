<?php

namespace App\Common;

class Action
{
    const CREATE = 'Create';
    const EDIT = 'Edit';
    const DELETE = 'Delete';
    const VIEW = 'View';

    /**
     * @return array
     */
    public static function getArray(): array
    {
        return [
            'CREATE' => self::CREATE,
            'EDIT' => self::EDIT,
            'DELETE' => self::DELETE,
            'VIEW' => self::VIEW
        ];
    }
}
