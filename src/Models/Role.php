<?php

namespace Knovators\Authentication\Models;

use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;
use Knovators\Authentication\Common\CommonService;


/**
 * Class Role
 * @package Knovators\Authentication\Models
 */
class Role extends Model
{

    protected $table = 'roles';
    protected $fillable = ['code', 'name'];


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
        $config = config('authentication.db');
        if ($config === 'mongodb') {
            return $this->belongsToMany(CommonService::getClass('permission'), null,
            'role_ids', 'permission_ids');
        }
        return $this->belongsToMany(CommonService::getClass('permission'), 'permissions_roles',
            'role_id', 'permission_id');
    }

}
