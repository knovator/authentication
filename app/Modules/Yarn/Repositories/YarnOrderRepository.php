<?php

namespace App\Modules\Yarn\Repositories;

use App\Modules\Sales\Support\CommonReportService;
use App\Modules\Yarn\Models\YarnOrder;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class YarnSalesOrderRepository
 * @package App\Modules\Yarn\Repository
 */
class YarnOrderRepository extends BaseRepository
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
        return YarnOrder::class;
    }


    /**
     * @param      $input
     * @param bool $export
     * @param      $relations
     * @return \Illuminate\Database\Eloquent\Builder|Model|Builder|mixed
     * @throws RepositoryException
     * @throws Exception
     */
    public function getYarnOrderList($input, $relations, $export = false) {
        $this->applyCriteria();

        $orders = $this->model->with($relations)->with('threadQty')->select('yarn_sales_orders.*');

        if (isset($input['ids']) && (!empty($input['ids']))) {
            $orders = $orders->whereIn('id', $input['ids']);
        }
        if (isset($input['start_date'])) {
            $orders = $orders->whereDate('order_date', '>=', $input['start_date']);
        }

        if (isset($input['end_date'])) {
            $orders = $orders->whereDate('order_date', '<=', $input['end_date']);
        }


        if (isset($input['payment'])) {
            if ($input['payment'] == 'yes') {
                $orders = $orders->whereNotNull('challan_no');
            }
            if ($input['payment'] == 'no') {
                $orders = $orders->where('status_id', $input['delivered_id'])
                                 ->whereNull('challan_no');
            }
        }

        $orders = datatables()->of($orders);

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
