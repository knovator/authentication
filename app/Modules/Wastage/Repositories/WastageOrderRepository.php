<?php

namespace App\Modules\Wastage\Repositories;

use App\Modules\Wastage\Models\WastageOrder;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class WastageOrderRepository
 * @package App\Modules\Wastage\Repository
 */
class WastageOrderRepository extends BaseRepository
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
        return WastageOrder::class;
    }


    /**
     * @param      $input
     * @param bool $export
     * @return mixed
     * @throws RepositoryException
     * @throws Exception
     */
    public function wastageOrderList($input, $export = false) {
        $this->applyCriteria();

        $orders = $this->model->with([
            'customer.state:id,name,code,gst_code',
            'status:id,name,code',
            'design:id,design_no,quality_name',
            'recipeMeters',
        ])->select('wastage_orders.*');


        if (isset($input['ids']) && (!empty($input['ids']))) {
            $orders = $orders->whereIn('id', $input['ids']);
        }

        if (isset($input['start_date'])) {
            $orders = $orders->whereDate('order_date', '>=', $input['start_date']);
        }

        if (isset($input['end_date'])) {
            $orders = $orders->whereDate('order_date', '<=', $input['end_date']);
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
     * @param WastageOrder $wastageOrder
     * @return
     */
    public function usedStocks(WastageOrder $wastageOrder) {
        return $wastageOrder->orderStocks()
                            ->selectRaw('product_id,product_type,SUM(kg_qty) as used_stock')
                            ->groupBy('product_id', 'product_type')->get()->keyBy('product_id')
                            ->toArray();
    }

    /**
     * @param $input
     * @param $statusIds
     * @return
     */
    public function getOrderAnalysis($input, $statusIds) {
        return $this->model->selectRaw('status_id,count(*) as total')->groupBy('status_id')
                           ->whereIn('status_id', $statusIds)->get()->keyBy('status_id');
    }

}
