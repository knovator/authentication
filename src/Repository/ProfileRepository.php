<?php


namespace Knovators\Authentication\Repository;
use Knovators\Authentication\Common\CommonService;
use Knovators\Support\Traits\BaseRepository;



/**
 * Class ProfileRepository
 * @package Knovators\Authentication\Repository
 */
class ProfileRepository extends BaseRepository
{

    /**
     * @return string|null
     */
    public function model() {
        return CommonService::getClass('user_account');
    }

}
