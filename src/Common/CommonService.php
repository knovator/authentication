<?php


namespace Knovators\Authentication\Common;

use Exception;
use Knovators\Authentication\Http\Resources\User as UserResource;
use Knovators\Authentication\Models\Permission;
use Knovators\Authentication\Models\Role;
use Knovators\Authentication\Models\User;
use Knovators\Authentication\Models\MongoDbUser;
use Knovators\Authentication\Models\MongoDbRole;
use Knovators\Authentication\Models\MongoDbPermission;
use Illuminate\Http\Request;

use Knovators\Authentication\Constants\ServiceType;
use Knovators\Authentication\Models\UserAccount;
use RobinCSamuel\LaravelMsg91\LaravelMsg91;

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
            case 'user_account':
                return self::getClassByName('model.userAccount', UserAccount::class);

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


    /**
     * @param $input
     * @throws Exception
     */
    public static function sendMessage($input) {
        $otp = rand(100000, 999999);
        $laravelMsg91 = self::laravelMsg91();
        $message = __('messages.otp_message', [
            'otp' => $otp,
            'url' => config('authentication.front_url') . DIRECTORY_SEPARATOR . "reset-password"
        ]);
        $laravelMsg91->sendOtp($input['phone'], $otp, $message);
    }


    /**
     * @return LaravelMsg91
     */
    private static function laravelMsg91() {
        return new LaravelMsg91;
    }


    /**
     * @param $input
     * @throws Exception
     */
    public static function resendOtp($input) {
        $laravelMsg91 = self::laravelMsg91();
        $laravelMsg91->resendOtp($input['phone']);
    }

    /**
     * @param $input
     * @return string
     * @throws Exception
     */
    public static function verifyOtp($input) {
        $laravelMsg91 = self::laravelMsg91();

        return $laravelMsg91->verifyOtp($input['phone'], $input['otp']);
    }
}
