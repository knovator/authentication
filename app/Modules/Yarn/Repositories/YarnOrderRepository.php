<?php

namespace App\Modules\Yarn\Repositories;

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
            $orders = $orders->whereDate('created_at', '>=', $input['start_date']);
        }

        if (isset($input['end_date'])) {
            $orders = $orders->whereDate('created_at', '<=', $input['end_date']);
        }


        if (isset($input['payment'])) {
            if ($input['payment'] == 'yes') {
                $orders = $orders->whereNotNull('challan_no');
            }
            if ($input['payment'] == 'no') {
                $orders = $orders->whereNull('challan_no');
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

}
