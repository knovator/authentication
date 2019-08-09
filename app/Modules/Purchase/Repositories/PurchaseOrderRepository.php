<?php

namespace App\Modules\Purchase\Repositories;

use App\Modules\Purchase\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Knovators\Support\Criteria\OrderByDescId;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class PurchaseOrderRepository
 * @package App\Modules\Purchase\Repository
 */
class PurchaseOrderRepository extends BaseRepository
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
        return PurchaseOrder::class;
    }


    /**
     * @param array $input
     * @param       $export
     * @return mixed
     * @throws RepositoryException
     * @throws \Exception
     */
    public function getPurchaseOrderList($input, $export = false) {
        $this->applyCriteria();
        $orders = $this->model->with([
            'threadQty',
            'threads.threadColor.thread',
            'threads.threadColor.color:id,name,code',
            'customer.state:id,name,code,gst_code',
            'status:id,name,code'
        ])->select('purchase_orders.*');

        if (isset($input['ids']) && (!empty($input['ids']))){
            $orders = $orders->whereIn('id',$input['ids']);
        }

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
