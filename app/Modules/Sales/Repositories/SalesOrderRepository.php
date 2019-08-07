<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class SalesOrderRepository
 * @package App\Modules\Sales\Repository
 */
class SalesOrderRepository extends BaseRepository
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
        return SalesOrder::class;
    }

    /**
     * @param $deliveredId
     * @param $manufacturingId
     * @param $pendingId
     * @return mixed
     * @throws RepositoryException
     */
    public function getSalesOrderList($deliveredId, $manufacturingId) {
        $this->applyCriteria();
        $orders = datatables()->of($this->model->with([
            'customer.state:id,name,code,gst_code',
            'status:id,name,code',
            'design:id,design_no,quality_name',
            'deliveries:id,delivery_no,delivery_date,sales_order_id',
            'recipeMeters',
        ])->with([
            'manufacturingTotalMeters' => function ($manufacturing) use ($manufacturingId) {
                /** @var Builder $manufacturing */
                $manufacturing->where('deliveries.status_id', $manufacturingId);
            },
            'deliveredTotalMeters'     => function ($delivered) use ($deliveredId) {
                /** @var Builder $delivered */
                $delivered->where('deliveries.status_id', $deliveredId);
            }
        ])->select('sales_orders.*'))
                              ->addColumn('pending_meters', function (SalesOrder $salesOrder) {
                                  return $salesOrder->pending_meters;
                              })->make(true);
        $this->resetModel();

        return $orders;
    }

}
