<?php


namespace App\Repositories;


use Knovators\Masters\Repository\MasterRepository as BaseRepository;

/**
 * Class MasterRepository
 * @package App\Repositories
 */
class MasterRepository extends BaseRepository
{


    /**
     * @param array $codes
     * @return array
     */
    public function getIdsByCode(array $codes) {
        return $this->model->whereIn('code', '=', $codes)->pluck('id')->toArray();
    }
}
