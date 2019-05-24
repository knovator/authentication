<?php

namespace App\Modules\Thread\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Masters\Models\Master;

/**
 * Class ThreadColor
 * @package App\Modules\Thread\Models
 */
class ThreadColor extends Model
{

    use SoftDeletes;

    protected $table = 'threads_colors';

    public $timestamps = false;

    protected $fillable = [
        'color_id',
        'thread_id',
    ];


    /**
     * @return mixed
     */
    public function color() {
        return $this->belongsTo(Master::class, 'color_id', 'id');
    }

    /**
     * @return mixed
     */
    public function thread() {
        return $this->belongsTo(Thread::class, 'thread_id', 'id');
    }

}
