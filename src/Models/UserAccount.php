<?php

namespace Knovators\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Knovators\Authentication\Notifications\VerificationUserAccountNotification;

/**
 * Class UserAccount
 * @package App
 */
class UserAccount extends Model
{

    use Notifiable;
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

    /**
     * @param $hashKey
     */
    public function sendVerificationMail($hashKey) {
        $this->notify(new VerificationUserAccountNotification($this, $hashKey));
    }

}
