<?php

namespace App\Modules\Purchase\Repositories;

use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Sales\Support\CommonReportService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class PurchaseOrderRepository
 * @package App\Modules\Purchase\Repository
 */
class PurchaseOrderRepository extends BaseRepository
{
    use CommonReportService;

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
        return PurchaseOrder::class;
    }


    /**
     * @param array $input
     * @param bool  $export
     * @return mixed
     * @throws RepositoryException
     * @throws Exception
     */
    public function getPurchaseOrderList($input, $export = false) {
        $this->applyCriteria();
        $orders = $this->model->with([
            'threadQty',
            'threads.threadColor.thread',
            'threads.threadColor.color:id,name,code',
            'customer.state:id,name,code,gst_code',
            'status:id,name,code'
        ])->select('purchase_orders.*')->withCount('deliveries');

        if ($export) {
            $orders = $orders->with([
                'deliveries.partialOrders.purchasedThread.threadColor' =>
                    function ($threadColor) {
                        /** @var Builder $threadColor */
                        $threadColor->with(['thread:id,name,denier', 'color:id,name']);
                    }
            ]);
        }

        if (isset($input['ids']) && (!empty($input['ids']))) {
            $orders = $orders->whereIn('id', $input['ids']);
        }

        if (isset($input['start_date'])) {
            $orders = $orders->whereDate('order_date', '>=', $input['start_date']);
        }

        if (isset($input['end_date'])) {
            $orders = $orders->whereDate('order_date', '<=', $input['end_date']);
        }

        if ($export) {
            $orders = datatables()->of($orders)->skipPaging();
        } else {

            $orders = datatables()->of($orders->with('deliveredMeters'))
                                  ->addColumn('pending_kg', function ($order) {
                                      return $order->pending_kg;
                                  });
        }

        $orders = $orders->make(true);

        $this->resetModel();

        return $orders;
    }


    /**
     * @param $customerId
     * @param $input
     * @return Builder|Model|mixed
     */
    public function customerOrders($customerId, $input) {

        $orders = $this->model->with([
            'status:id,name,code',
            'quantity'
        ])->select(['id', 'order_no', 'order_date', 'status_id'])
                              ->where('customer_id', '=', $customerId);


        if (isset($input['ids']) && (!empty($input['ids']))) {
            $orders = $orders->whereIn('id', $input['ids']);
        }

        if (isset($input['start_date'])) {
            $orders = $orders->whereDate('order_date', '>=', $input['start_date']);
        }

        if (isset($input['end_date'])) {
            $orders = $orders->whereDate('order_date', '<=', $input['end_date']);
        }

        return $orders->orderByDesc('id');
    }


    /**
     * @param $input
     * @param $statuses
     * @return
     */
    public function getOrderAnalysis($input, $statuses) {
        $columns = '';
        $condition = '';
        $lastKey = array_key_last($statuses);
        foreach ($statuses as $statusKey => $status) {
            $alias = strtolower($status->code);
            $columns .= ",COUNT(IF(status_id = {$status->id},id,null)) as {$alias}_orders,SUM(IF(status_id = {$status->id},total_kg,0)) as {$alias}_kg";
            $condition .= 'status_id = ' . $status->id . ($statusKey != $lastKey ? ' OR ' : '');
        }

        return $this->model->selectRaw("COUNT(IF({$condition},id,null)) as total_orders,SUM(IF({$condition},total_kg,0)) as total_kg" .
            $columns)->whereDate('order_date', '>=', $input['startDate'])
                           ->whereDate('order_date', '<=', $input['endDate'])->first();
    }


}
