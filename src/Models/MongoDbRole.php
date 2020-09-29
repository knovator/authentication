<?php

namespace Knovators\Authentication\Models;

use Illuminate\Database\Eloquent\Builder;
use Knovators\Authentication\Common\CommonService;
use Jenssegers\Mongodb\Eloquent\Model;


/**
 * Class Role
 * @package Knovators\Authentication\Models
 */
class MongoDbRole extends Model
{

    protected $collection = 'roles';
     protected $fillable = ['code','name'];

    /**
     * @param $query
     * @param $code
     * @return mixed
     */
    public function scopeWhereRole($query, $code) {
        /** @var Builder $query */
        if (is_array($code)) {
            return $query->whereIn('code', $code);
        }

        return $query->where('code', $code);
    }


    /**
     * @return mixed
     */
    public function permissions() {
        return $this->belongsToMany(CommonService::getClass('permission'), 'permissions_roles',
            'role_id', 'permission_id');
    }

}