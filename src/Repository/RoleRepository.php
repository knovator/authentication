<?php


namespace Knovators\Authentication\Repository;


use Knovators\Authentication\Common\CommonService;
use Knovators\Support\Traits\BaseRepository;

/**
 * Class RoleRepository
 * @package Knovators\Authentication\Repository
 */
class RoleRepository extends BaseRepository
{

    /**
     * Configure the Model
     *
     **/
    public function model() {
        return CommonService::getClass('role');
    }

}
