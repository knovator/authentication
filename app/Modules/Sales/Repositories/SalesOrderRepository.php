<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Dashboard\Http\Resources\TopCustomer;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Support\CommonReportService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class SalesOrderRepository
 * @package App\Modules\Sales\Repository
 */
class SalesOrderRepository extends BaseRepository
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
        return SalesOrder::class;
    }

    /**
     * @param      $deliveredId
     * @param      $manufacturingIds
     * @param      $input
     * @param bool $export
     * @return mixed
     * @throws RepositoryException
     */
    public function getSalesOrderList($deliveredId, $manufacturingIds, $input, $export = false) {
        $this->applyCriteria();

        $orders = $this->model->with([
            'customer.state:id,name,code,gst_code',
            'status:id,name,code',
            'design:id,design_no,quality_name',
            'deliveries:id,delivery_no,delivery_date,sales_order_id',
            'recipeMeters',
            'designBeam:id,thread_color_id',
        ])->with([
            'manufacturingTotalMeters' => function ($manufacturing) use ($manufacturingIds) {
                /** @var Builder $manufacturing */
                $manufacturing->whereIn('deliveries.status_id', $manufacturingIds);
            },
            'deliveredTotalMeters'     => function ($delivered) use ($deliveredId) {
                /** @var Builder $delivered */
                $delivered->where('deliveries.status_id', $deliveredId);
            }
        ])->select('sales_orders.*');


        if (isset($input['ids']) && (!empty($input['ids']))) {
            $orders = $orders->whereIn('id', $input['ids']);
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

    /**
     * @param $customerId
     * @param $input
     * @return Builder|Model|\Illuminate\Database\Query\Builder|mixed
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
            $columns .= ",COUNT(IF(status_id = {$status->id},id,null)) as {$alias}_orders,SUM(IF(status_id = {$status->id},total_meters,0)) as {$alias}_meters";
            $condition .= 'status_id = ' . $status->id . ($statusKey != $lastKey ? ' OR ' : '');
        }

        return $this->model->selectRaw("COUNT(IF({$condition},id,null)) as total_orders,SUM(IF({$condition},total_meters,0)) as total_meters" .
            $columns)->whereDate('order_date', '>=', $input['startDate'])
                           ->whereDate('order_date', '<=', $input['endDate'])->first();
    }


    /**
     * @param      $input
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function topCustomerReport($input) {

        $orders = $this->model->selectRaw('customer_id,SUM(total_meters) as meters,COUNT(id) as orders')
                              ->with('customer:id,first_name,last_name,email,phone')
                              ->groupBy('customer_id')->orderByRaw('meters DESC');


        if (isset($input['ids']) && (!empty($input['ids']))) {
            $orders = $orders->whereIn('customer_id', $input['ids']);
        }

        if ($input['type'] == 'chart') {

            if ($input['api'] == 'dashboard') {
                $orders = $orders->whereDate('order_date', '>=', $input['startDate'])
                                 ->whereDate('order_date', '<=', $input['endDate']);
            }

            return TopCustomer::collection($orders->take($input['length'])
                                                  ->get());
        }
        if ($input['type'] == 'export') {
            return datatables()->of($orders)->skipPaging()->make(true)->getData()->data;
        }

        return datatables()->of($orders)->make(true);
    }

    /**
     * @param $input
     * @return
     * @throws Exception
     */
    public function mostUsedDesignReport($input) {
        $now = Carbon::now();
        $input['endDate'] = $now->format('Y-m-d');
        $input['startDate'] = $now->subMonths(6)->format('Y-m-d');

        return datatables()->of($this->model->selectRaw('design_id,COUNT(id) as design_count')
                                            ->whereDate('order_date', '>=', $input['startDate'])
                                            ->whereDate('order_date', '<=', $input['endDate'])
                                            ->with([
                                                'design.detail:design_id,designer_no,avg_pick,reed',
                                                'design.mainImage.file:id,uri'
                                            ])->groupBy('design_id')
                                            ->orderByDesc('design_count'))->make(true);

    }


}
