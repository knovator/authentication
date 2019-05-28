<?php

namespace App\Modules\Design\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class Design
 * @package App\Modules\Design\Models
 */
class Design extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'designs';

    protected $fillable = [
        'quality_name',
        'type_id',
        'fiddles',
        'is_active',
        'is_approved',
        'created_by',
        'deleted_by',
    ];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'deleted_at',
        'created_at',
        'updated_at'
    ];


    /**
     * @return mixed
     */
    public function beams() {
        return $this->hasMany(DesignBeam::class, 'design_id', 'id');
    }


    /**
     * @return mixed
     */
    public function images() {
        return $this->hasMany(DesignImage::class, 'design_id', 'id');
    }

    /**
     * @return mixed
     */
    public function fiddlePicks() {
        return $this->hasMany(DesignFiddlePick::class, 'design_id', 'id');
    }

    /**
     * @return mixed
     */
    public function detail() {
        return $this->hasOne(DesignDetail::class, 'design_id', 'id');
    }

}
