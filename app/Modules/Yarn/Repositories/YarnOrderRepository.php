<?php

namespace App\Modules\Yarn\Repositories;

use App\Modules\Yarn\Models\YarnOrder;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class YarnSalesOrderRepository
 * @package App\Modules\Yarn\Repository
 */
class YarnOrderRepository extends BaseRepository
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
        return YarnOrder::class;
    }


}
