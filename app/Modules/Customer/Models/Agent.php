<?php

namespace App\Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Agent
 * @package App\Modules\Customer\Models
 */
class Agent extends Model
{

    protected $table = 'agents';

    protected $fillable = [
        'name',
        'slug',
        'contact_number'
    ];
}
