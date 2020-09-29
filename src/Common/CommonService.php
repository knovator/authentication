<?php


namespace Knovators\Authentication\Common;

use Knovators\Authentication\Http\Resources\User as UserResource;
use Knovators\Authentication\Models\Permission;
use Knovators\Authentication\Models\Role;
use Knovators\Authentication\Models\User;
use Knovators\Authentication\Models\MongoDbUser;
use Knovators\Authentication\Models\MongoDbRole;
use Knovators\Authentication\Models\MongoDbPermission;
use Illuminate\Http\Request;

use Knovators\Authentication\Constants\ServiceType;

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

            case ServiceType::USER:
                return self::getClassByName('models.user', self::getModel('user'));
            case ServiceType::ROLE:
                return self::getClassByName('models.role', self::getModel('role'));
            case ServiceType::MEDIA:
                return config('media.model');
            case ServiceType::PERMISSION:
                return self::getClassByName('models.permission', self::getModel('permission'));
                //TODO please check below update to production server.
            case 'media':
                return self::getClassByName('models.media', self::getModel('media'));
            case ServiceType::USER_RESOURCE:
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

    /**
     * @param $model
     * @return string
     */
    private static function getModel($class) {
        return config('authentication.' . $class);
    }
}
