<?php


namespace Knovators\Authentication\Repository;


use Knovators\Authentication\Common\CommonService;
use Knovators\Support\Traits\BaseRepository;

/**
 * Class UserRepository
 * @package Knovators\Authentication\Repository
 */
class UserRepository extends BaseRepository
{

    /**
     * Configure the Model
     *
     **/
    public function model() {
        return CommonService::getClass('user');
    }

    public function getPermissions($user,$role)
    {
            $user = $this->model->find($user)->first();
            $user->load('permissions');
            $userPermission = $user->permissions;
            if ($userPermission->isEmpty()){
               $this->getPermissionsByRole($role);
            }       
            return $userPermission->pluck('id')->toArray();       
    }

    public function getPermissionsByRole($role)
    {
            $role->load('permissions');
            $rolePermission = $role->permissions;
            return $rolePermission->pluck('id')->toArray();
       
    }
}
