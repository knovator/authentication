<?php

namespace Knovators\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * @return bool
     */
    public function isVerified() {
        return $this->is_verified == 1;
    }

}
