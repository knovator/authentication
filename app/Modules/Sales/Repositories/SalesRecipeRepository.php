<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\SalesOrderRecipe;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class SalesRecipeRepository
 * @package App\Modules\Sales\Repository
 */
class SalesRecipeRepository extends BaseRepository
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
        return SalesOrderRecipe::class;
    }

    /**
     * @param $orderRecipeIds
     * @return mixed
     */
    public function removeById($orderRecipeIds) {
        return $this->model->whereIn('id',
            $orderRecipeIds)->delete();
    }


    /**
     * @param       $salesOrderId
     * @param array $ids
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]
     */
    public function getOrderRecipeList($salesOrderId, $ids = []) {
        $orderRecipes = $this->model->with('remainingQuantity')->where('sales_order_id', '=',
            $salesOrderId);
        if (!empty($ids)) {
            $orderRecipes = $orderRecipes->whereIn('id', $ids);
        }

        return $orderRecipes->get();
    }

}
