<?php

namespace App\Support\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class OrderByNameCriteria.
 *
 * @package namespace App\Criteria;
 */
class OrderByNameCriteria implements CriteriaInterface
{

    /**
     * Apply criteria in query repository
     *
     * @param string              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository) {
        $model = $model->orderBy('name','asc');
        return $model;
    }
}
