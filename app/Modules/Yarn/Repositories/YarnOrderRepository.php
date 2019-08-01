<?php

namespace App\Modules\Yarn\Repositories;

use App\Modules\Yarn\Models\YarnOrder;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class YarnSalesOrderRepository
 * @package App\Modules\Yarn\Repository
 */
class YarnOrderRepository extends BaseRepository
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
        return YarnOrder::class;
    }


    /**
     * @param      $input
     * @param bool $export
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getYarnOrderList($input, $export = false) {
        $this->applyCriteria();
        $orders = $this->model->with([
            'threadQty',
            'threads.threadColor.thread',
            'threads.threadColor.color:id,name,code',
            'customer.state:id,name,code,gst_code',
            'status:id,name,code'
        ])->select('yarn_sales_orders.*');


        if (isset($input['start_date'])) {
            $orders = $orders->whereDate('created_at', '>=', $input['start_date']);
        }

        if (isset($input['end_date'])) {
            $orders = $orders->whereDate('created_at', '<=', $input['end_date']);
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
