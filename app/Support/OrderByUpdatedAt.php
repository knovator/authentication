<?php

namespace App\Support;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class OrderByDescId.
 *
 * @package Knovators\Support\Criteria
 */
class OrderByUpdatedAt implements CriteriaInterface
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
        $model = $model->orderByDesc('updated_at');
        return $model;
    }
}
