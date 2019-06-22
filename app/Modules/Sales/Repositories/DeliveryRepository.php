<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\Delivery;
use App\Modules\Sales\Models\SalesOrderRecipe;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class DeliveryRepository
 * @package App\Modules\Sales\Repository
 */
class DeliveryRepository extends BaseRepository
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
        return Delivery::class;
    }


}
