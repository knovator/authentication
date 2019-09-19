<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\Delivery;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
     * @param $delivery
     * @return
     */
    public function usedStocks(Delivery $delivery) {
        return $delivery->orderStocks()
                        ->selectRaw('product_id,product_type,SUM(kg_qty) as used_stock')
                        ->groupBy('product_id', 'product_type')->get()->keyBy('product_id')
                        ->toArray();
    }


    /**
     * @param $salesOrderId
     * @return
     * @throws RepositoryException
     * @throws Exception
     */
    public function getDeliveryList($salesOrderId) {
        $this->applyCriteria();
        $deliveries = datatables()->of($this->model->where('sales_order_id', $salesOrderId)
                                                   ->with($this->commonRelations()))
                                  ->editColumn('partial_orders', function ($delivery) {

                                      /** @var Collection $partialOrders */
                                      $delivery->partialOrders->map(function ($partialOrder) {

                                          dd($partialOrder->assignedMachine);
                                          if (!is_null($partialOrder->assignedMachine)) {
                                              $partialOrder->machine = $partialOrder->assignedMachine;
                                          }
                                          unset($partialOrder->assignedMachine);
                                      });

                                      return $delivery->partialOrders->toArray();
                                  })->make(true);
        $this->resetModel();

        return $deliveries;


    }


    /**
     * @return array
     */
    public function commonRelations() {
        return [
            'status:id,name,code',
            'partialOrders' => function ($partialOrders) {
                $partialOrders->with([
                    'machine:id,name,panno,reed',
                    'assignedMachine:id,name,panno,reed,partial_order_id',
                    'orderRecipe.recipe.fiddles.thread:id,name,denier',
                    'orderRecipe.recipe.fiddles.color:id,name,code'
                ]);
            }

        ];
    }


    /**
     * @param $partialOrderIds
     * @param $salesOrderId
     */
    public function removeSinglePartialOrders($partialOrderIds, $salesOrderId) {
        $this->model->newQuery()->whereDoesntHave('partialOrders',
            function ($partialOrders) use ($partialOrderIds) {
                /** @var Builder $partialOrders */
                $partialOrders->whereKeyNot($partialOrderIds);
            })->where('sales_order_id', '=', $salesOrderId)->delete();
    }
}
