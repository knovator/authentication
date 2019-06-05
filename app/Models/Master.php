<?php

namespace App\Models;

use App\Modules\Thread\Models\Thread;
use App\Modules\Thread\Models\ThreadColor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Knovators\Masters\Models\Master as Model;

/**
 * Class Master
 *
 * @package Knovators\Masters\Models
 */
class Master extends Model
{


    /**
     *  Parent to child has many relationship
     * @return HasMany
     */
    public function threadColors() {
        return $this->hasMany(ThreadColor::class, 'color_id', 'id');
    }


    /**
     *  Parent to child has many relationship
     * @return HasMany
     */
    public function threadType() {
        return $this->hasMany(Thread::class, 'type_id', 'id');
    }


}
