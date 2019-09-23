<?php

namespace App\Modules\Wastage\Repositories;

use App\Modules\Wastage\Models\WastageOrderRecipe;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class WastageOrderRecipeRepository
 * @package App\Modules\Wastage\Repository
 */
class WastageOrderRecipeRepository extends BaseRepository
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
        return WastageOrderRecipe::class;
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function deleteRecipeById($ids) {
        return $this->model->whereIn('id', $ids)->delete();
    }

}
