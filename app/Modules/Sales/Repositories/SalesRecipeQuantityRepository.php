<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\SalesOrderQuantity;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class SalesRecipeRepository
 * @package App\Modules\Sales\Repository
 */
class SalesRecipeQuantityRepository extends BaseRepository
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
        return SalesOrderQuantity::class;
    }

}
