<?php

namespace App\Modules\Design\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Media\Models\Media;

/**
 * Class DesignImage
 * @package App\Modules\DesignDetail\Models
 */
class DesignImage extends Model
{
    public $timestamps = false;

    use SoftDeletes;

    protected $table = 'design_images';

    protected $fillable = [
        'design_id',
        'file_id',
        'type'
    ];
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file() {
        return $this->belongsTo(Media::class, 'file_id', 'id')->select([
            'id',
            'name',
            'type',
            'mime_type',
            'uri'
        ]);
    }

}
