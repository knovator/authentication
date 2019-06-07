<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\RecipePartialOrder;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class RecipePartialRepository
 * @package App\Modules\Sales\Repository
 */
class RecipePartialRepository extends BaseRepository
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
        return RecipePartialOrder::class;
    }

}
