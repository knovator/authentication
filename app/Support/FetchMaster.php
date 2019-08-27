<?php


namespace App\Support;


use App\Models\Master;
use Illuminate\Container\Container;
use Knovators\Masters\Repository\MasterRepository;
use Prettus\Repository\Exceptions\RepositoryException;


/**
 * Trait FetchMaster
 * @package App\Modules\Support
 */
trait FetchMaster
{


    /**
     * @param $code
     * @return mixed
     */
    public function retrieveMasterId($code) {
        return $this->getMasterRepository()->findByCode($code)->id;
    }

    /**
     * @return MasterRepository
     */
    private function getMasterRepository() {
        return new MasterRepository(new Container());
    }

    /**
     * @param $codes
     * @return mixed
     * @throws RepositoryException
     */
    public function findMasterByCode(array $codes) {
        return $this->getMasterRepository()->makeModel()->whereIn('code',  $codes)->pluck('id')
                    ->toArray();
    }


}
