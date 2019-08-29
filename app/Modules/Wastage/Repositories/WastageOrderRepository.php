<?php

namespace App\Modules\Wastage\Repositories;

use App\Modules\Wastage\Models\WastageOrder;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class WastageOrderRepository
 * @package App\Modules\Wastage\Repository
 */
class WastageOrderRepository extends BaseRepository
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
        return WastageOrder::class;
    }


}
