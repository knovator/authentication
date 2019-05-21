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

}
