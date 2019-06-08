<?php


namespace App\Support;


use Illuminate\Container\Container;
use Knovators\Masters\Repository\MasterRepository;


/**
 * Trait FetchMaster
 * @package App\Modules\Support
 */
trait FetchMaster
{


    /**
     * @return MasterRepository
     */
    private function getMasterRepository() {
        return new MasterRepository(new Container());
    }

    /**
     * @param $code
     * @return mixed
     */
    public function retrieveMasterId($code) {
        return $this->getMasterRepository()->findByCode($code)->id;
    }





}
