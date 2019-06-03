<?php

namespace App\Modules\Machine\Repositories;

use App\Modules\Machine\Models\Machine;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class MachineRepository
 * @package App\Modules\Machine\Repository
 */
class MachineRepository extends BaseRepository
{

    /**
     * @throws RepositoryException
     */
    public function boot() {
        $this->pushCriteria(OrderByDescId::class);
    }

    /**
     * Configure the Model
     *
     **/
    public function model() {
        return Machine::class;
    }


    /**
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getMachineList() {



    }

}
