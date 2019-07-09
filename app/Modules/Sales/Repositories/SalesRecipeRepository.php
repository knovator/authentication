<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\SalesOrderRecipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
     * @param null  $skipDeliveryId
     * @param       $loadRelation
     * @return Builder[]|Collection|Model[]
     */
    public function getOrderRecipeList(
        $salesOrderId,
        $skipDeliveryId = null,
        $loadRelation = false
    ) {
        $orderRecipes = $this->model->with([
            'remainingQuantity' => function ($remainingQuantity) use ($skipDeliveryId) {
                /** @var Builder $remainingQuantity */
                if (isset($skipDeliveryId)) {
                    $remainingQuantity->where('delivery_id', '<>', $skipDeliveryId);
                }
            }
        ])->where('sales_order_id', '=',
            $salesOrderId);


        if ($loadRelation){
            $orderRecipes = $orderRecipes->with(['recipe.fiddles.thread','recipe.fiddles.color']);
        }

        return $orderRecipes->get();
    }

}
