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
     * @param $salesOrderId
     * @return
     */
    public function getOrderRecipeList($salesOrderId) {
        return $this->model->with('remainingQuantity')->where('sales_order_id', '=', $salesOrderId)
                           ->get();

    }

}
