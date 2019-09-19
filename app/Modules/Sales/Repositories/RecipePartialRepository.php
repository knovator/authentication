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


    /**
     * @param $partialOrderIds
     * @return mixed
     */
    public function removeById($partialOrderIds) {
        return $this->model->whereIn('id',
            $partialOrderIds)->delete();
    }


    /**
     * @param $orderRecipeIds
     * @return mixed
     */
    public function findIdByRecipeIds($orderRecipeIds) {
        return $this->model->whereIn('sales_order_recipe_id',
            $orderRecipeIds)->pluck('id')->toArray();
    }


    /**
     * @param $field
     * @param $orderRecipeIds
     * @return mixed
     */
    public function removeByField($field, $orderRecipeIds) {
        return $this->model->whereIn($field, $orderRecipeIds)->delete();
    }


    /**
     * @param $deliveryId
     * @return
     */
    public function index($deliveryId) {
        return $this->model->where('delivery_id', $deliveryId)->with(['machine', 'assignedMachine'])->get();
    }


}
