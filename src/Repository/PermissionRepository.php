<?php


namespace Knovators\Authentication\Repository;


use Knovators\Authentication\Common\CommonService;
use Knovators\Support\Traits\BaseRepository;

/**
 * Class PermissionRepository
 * @package Knovators\Authentication\Repository
 */
class PermissionRepository extends BaseRepository
{

    /**
     * Configure the Model
     *
     **/
    public function model() {
        return CommonService::getClass('permission');
    }


    public function getIds($routeNames,$column){
        $permissionIds = $this->model->whereIn('route_name',$routeNames)
                                      ->pluck($column)
                                      ->toArray();
        return $permissionIds;
    }

}
