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
     * Applies the given where conditions to the model.
     *
     * @param array $where
     * @return void
     */
    public function deleteWhere(array $where) {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                $this->model = $this->model->where($field, $value);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

}
