<?php


namespace Knovators\Authentication\Repository;
use Knovators\Authentication\Common\CommonService;
use Knovators\Support\Traits\BaseRepository;


/**
 * Class AccountRepository
 * @package Knovators\Authentication\Repository
 */
class AccountRepository extends BaseRepository
{

    /**
     * @return string|null
     */
    public function model() {
        return CommonService::getClass('user_account');
    }

}
