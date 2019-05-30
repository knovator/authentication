<?php


namespace Knovators\Authentication\Common;

use Knovators\Authentication\Models\Permission;
use Knovators\Authentication\Models\Role;
use Knovators\Authentication\Models\User;
use Knovators\Authentication\Http\Resources\User as UserResource;

/**
 * Class CommonService
 * @package Knovators\Authentication\Common
 */
class CommonService
{

    /**
     * @param $classLabel
     * @return string|null
     */
    public static function getClass($classLabel) {
        switch ($classLabel) {

            case 'user':
                return self::getClassByName('models.user', User::class);

            case 'role':
                return self::getClassByName('models.role', Role::class);

            case 'permission':
                return self::getClassByName('models.permission', Permission::class);

            case 'user_resource':
                return self::getClassByName('resources.user' . $classLabel, UserResource::class);

            default:
                return null;
        }
    }


    /**
     * @param $classLabel
     * @param $path
     * @return string
     */
    private static function getClassByName($classLabel, $path) {
        if (!($class = self::getConfigAttribute($classLabel))) {
            return $path;
        }

        return $class;
    }


    /**
     * @param $class
     * @return string
     */
    private static function getConfigAttribute($class) {
        return config('authentication.' . $class);
    }
}
