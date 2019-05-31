<?php

namespace App\Modules\Thread\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Knovators\Masters\Models\Master;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class Thread
 * @package App\Modules\Thread\Models
 */
class Thread extends Model
{

    use SoftDeletes, Notifiable, HasModelEvent;

    protected $table = 'threads';

    protected $fillable = [
        'name',
        'company_name',
        'denier',
        'type_id',
        'price',
        'is_active',
        'created_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'deleted_by'
    ];

    /**
     * @return mixed
     */
    public function type() {
        return $this->belongsTo(Master::class, 'type_id', 'id');
    }


    /**
     * @return mixed
     */
    public function threadColors() {
        return $this->hasMany(ThreadColor::class, 'thread_id', 'id');
    }


    /**
     * @return mixed
     */
    public function colors() {
        return $this->belongsToMany(Master::class, 'threads_colors', 'thread_id', 'color_id');
    }

}
