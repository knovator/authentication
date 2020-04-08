<?php

namespace Knovators\Authentication\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Knovators\Authentication\Common\CommonService;
use Knovators\Authentication\Notifications\ResetPasswordNotification;
use Knovators\Authentication\Notifications\VerificationUserNotification;
use Knovators\Media\Models\Media;
use Knovators\Support\Traits\HasModelEvent;
use Knovators\Support\Traits\HasSlug;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 * @package Knovators\Authentication\Models
 */
class User extends Authenticatable
{

    use SoftDeletes, HasApiTokens, Notifiable, HasSlug, HasModelEvent;
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
        'password',
        'roles',
        'phone',
        'created_by',
        'deleted_by',
        'image_id'
    ];
    protected $table = 'users';

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
    protected $attributes = [
        'is_active'   => 1
    ];

    protected $casts = [
        'is_active'   => 'boolean'
    ];


    protected $slugifyColumns = ['first_name', 'last_name', 'id'];


    /**
     * @return bool
     */
    public function isVerified() {
        return $this->primaryAccount->is_verified == 1;
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
        return $this->is_active == 1;
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
        $config = config('authentication.db');
        if ($config === 'mongodb') {
            return $this->embedsMany(CommonService::getClass('role'));
        }

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
     * @return BelongsTo
     */
    public function image() {
        return $this->belongsTo(Media::class, 'image_id', 'id')->select([
            'id',
            'name',
            'type',
            'mime_type'
        ]);
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token) {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @param $hashKey
     */
    public function sendVerificationMail($hashKey) {
        $this->notify(new VerificationUserNotification($this, $hashKey));
    }


    /**
     * @return HasOne
     */
    public function primaryAccount() {
        return $this->hasOne(CommonService::getClass('user_account'), 'user_id', 'id')
                    ->where('default', true);
    }


    /**
     * @return HasMany
     */
    public function userAccounts() {
        return $this->hasMany(CommonService::getClass('user_account'), 'user_id', 'id');
    }


}

