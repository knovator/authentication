<?php

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\SalesOrder;
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
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getSalesOrderList() {
        $this->applyCriteria();
        $orders = datatables()->of($this->model->with([
            'customer.state:id,name,code,gst_code',
            'status:id,name,code',
            'design:id,design_no,quality_name',
            'deliveries:id,delivery_no,delivery_date,sales_order_id',
            'recipeMeters',
        ])->with('manufacturingMeters')->select('sales_orders.*'))->make(true);
        $this->resetModel();

        return $orders;
    }

}
