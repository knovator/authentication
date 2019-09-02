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
     * @param      $deliveredId
     * @param      $manufacturingId
     * @param      $input
     * @param bool $export
     * @return mixed
     * @throws RepositoryException
     */
    public function getSalesOrderList($deliveredId, $manufacturingId, $input, $export = false) {
        $this->applyCriteria();

        $orders = $this->model->with([
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
        ])->select('sales_orders.*');


        if (isset($input['ids']) && (!empty($input['ids']))){
            $orders = $orders->whereIn('id',$input['ids']);
        }

        if (isset($input['start_date'])) {
            $orders = $orders->whereDate('order_date', '>=', $input['start_date']);
        }

        if (isset($input['end_date'])) {
            $orders = $orders->whereDate('order_date', '<=', $input['end_date']);
        }


        $orders = datatables()->of($orders)
                              ->addColumn('pending_meters', function (SalesOrder $salesOrder) {
                                  return $salesOrder->pending_meters;
                              });

        if ($export) {
            $orders = $orders->skipPaging();
        }

        $orders = $orders->make(true);
        $this->resetModel();

        return $orders;
    }

}
