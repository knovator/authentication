<?php

namespace Knovators\Authentication\Models;


use Illuminate\Database\Eloquent\SoftDeletes;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Knovators\Authentication\Common\CommonService;
use Knovators\Authentication\Notifications\ResetPasswordNotification;
use Knovators\Media\Models\Media;
use Knovators\Support\Traits\HasModelEvent;
//use Knovators\Support\Traits\HasSlug;
use Knovators\Support\Traits\HasSlug;
use Laravel\Passport\HasApiTokens;
use Knovators\Authentication\Notifications\VerificationUserNotification;

/**
 * Class User
 * @package Knovators\Authentication\Models
 */
class MongoDbUser extends Authenticatable
{

    use Notifiable, SoftDeletes, HasApiTokens, Notifiable, HasSlug, HasModelEvent;
    const ACCEPTABLE = 1;
    const NOT_ACCEPTABLE = 0;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'slug',
        'is_active',
        'email',
        'email_verification_key',
        'email_verified',
        'password',
        'phone',
        'created_by',
        'deleted_by',
        'image_id'
    ];
    protected $collection = 'users';
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $slugColumn = 'slug';
    protected $appends = ['full_name'];
    protected $slugifyColumns = ['first_name', 'last_name', 'id'];
    protected $attributes = [
        'is_active'      => self::ACCEPTABLE,
        'email_verified' => self::NOT_ACCEPTABLE,
    ];

    /**
     * @return bool
     */
    public function emailVerified() {
        return $this->email_verified == self::ACCEPTABLE;
    }

    /**
     * @return string
     */
    public function getFullNameAttribute() {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @return bool
     */
    public function isActive() {
        return $this->is_active == self::ACCEPTABLE;
    }

    /**
     * @param $role
     * @return mixed
     */
    public function hasRole($role) {
        return $this->roles()->whereRole($role)->first() ? true : false;
    }

    /**
     * @return mixed
     */
    public function roles() {

        return $this->belongsToMany(CommonService::getClass('role'), 'users_roles', 'user_id',
            'role_id');
    }


    /**
     * @return mixed
     */
    public function orderByRoles() {

        return $this->roles()->orderBy('weight');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function image() {
        return $this->belongsTo(CommonService::getClass('media'), 'image_id', 'id')->select([
            'id',
            'name',
            'type',
            'mime_type'
        ]);
    }

    public function sendVerificationMail($hashKey) {
        $this->notify(new VerificationUserNotification($this, $hashKey));
    }
    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
//    public function sendPasswordResetNotification($token) {
//        $this->notify(new ResetPasswordNotification($token));
//    }


    public function routeNotificationForMail($notification) {
        return $this->email;
    }

}

