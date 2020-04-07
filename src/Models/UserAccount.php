<?php

namespace Knovators\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAccount
 * @package App
 */
class UserAccount extends Model
{

    protected $table = 'user_accounts';

    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'default',
        'is_verified',
        'email_verification_key'
    ];
}
