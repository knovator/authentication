<?php

namespace App\Modules\Wastage\Repositories;

use App\Modules\Wastage\Models\WastageOrder;
use Exception;
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
            'design:id,design_no,quality_name'
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


}
