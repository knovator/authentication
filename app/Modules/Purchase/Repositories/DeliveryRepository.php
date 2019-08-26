<?php

namespace App\Modules\Purchase\Repositories;

use App\Modules\Purchase\Models\PurchaseDelivery;
use Exception;
use Knovators\Support\Traits\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class PurchaseDeliveryRepository
 * @package App\Modules\Purchase\Repository
 */
class DeliveryRepository extends BaseRepository
{

    /**
     * Configure the Model
     *
     **/
    public function model() {
        return PurchaseDelivery::class;
    }

    /**
     * @param $purchaseOrderId
     * @return
     * @throws RepositoryException
     * @throws Exception
     */
    public function getDeliveryList($purchaseOrderId) {
        $this->applyCriteria();
        $deliveries = datatables()->of($this->model->where('purchase_order_id', $purchaseOrderId)->with
        ($this->commonRelations()))->make(true);
        $this->resetModel();

        return $deliveries;

    }

    /**
     * @return array
     */
    public function commonRelations() {
        return [
            'status:id,name,code',
            'partialOrders.purchasedThread.threadColor.thread:id,name,denier',
            'partialOrders.purchasedThread.threadColor.color:id,name,code',
        ];
    }


}
