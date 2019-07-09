<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\Delivery;
use App\Modules\Sales\Models\SalesOrderRecipe;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class DeliveryRepository
 * @package App\Modules\Sales\Repository
 */
class DeliveryRepository extends BaseRepository
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
        return Delivery::class;
    }


    /**
     * @param $salesOrderId
     * @return
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getDeliveryList($salesOrderId) {
        $this->applyCriteria();
        $deliveries = datatables()->of($this->model->where('sales_order_id', $salesOrderId)->with
        ([
            'status:id,name,code',
            'partialOrders' => function ($partialOrders) {
                $partialOrders->with([
                    'machine',
                    'orderRecipe.recipe.fiddles.thread:id,name,denier',
                    'orderRecipe.recipe.fiddles.color:id,name,code'
                ]);
            }

        ]))->make(true);
        $this->resetModel();

        return $deliveries;


    }


}
