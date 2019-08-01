<?php

namespace App\Modules\Yarn\Models;

use App\Modules\Thread\Models\ThreadColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class YarnOrderThread
 * @package App\Modules\Yarn\Models
 */
class YarnOrderThread extends Model
{

    public $timestamps = false;

    use SoftDeletes;

    protected $table = 'yarn_threads';

    protected $fillable = [
        'thread_color_id',
        'yarn_order_id',
        'kg_qty',
        'rate',
    ];


    protected $hidden = [
        'deleted_at',
    ];


    /**
     * @return mixed
     */
    public function threadColor() {
        return $this->belongsTo(ThreadColor::class, 'thread_color_id', 'id');
    }
}
