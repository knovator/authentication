<?php

namespace App\Modules\Purchase\Repositories;

use App\Modules\Purchase\Models\PurchaseOrderThread;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Knovators\Support\Traits\BaseRepository;

/**
 * Class PurchasedThreadRepository
 * @package App\Modules\Purchase\Repository
 */
class PurchasedThreadRepository extends BaseRepository
{

    /**
     * Configure the Model
     *
     **/
    public function model() {
        return PurchaseOrderThread::class;
    }

    /**
     * @param       $purchaseOrderId
     * @param null  $skipDeliveryId
     * @param       $loadRelation
     * @return Collection
     */
    public function getPurchaseOrderList(
        $purchaseOrderId,
        $skipDeliveryId = null,
        $loadRelation = false
    ) {
        $purchasedThreads = $this->model->with([
            'remainingQuantity' => function ($remainingQuantity) use ($skipDeliveryId) {
                /** @var Builder $remainingQuantity */
                if (isset($skipDeliveryId)) {
                    $remainingQuantity->where('delivery_id', '<>', $skipDeliveryId);
                }
            }
        ])->where('purchase_order_id', '=', $purchaseOrderId);


        if ($loadRelation) {
            $purchasedThreads = $purchasedThreads->with([
                'threadColor.thread',
                'threadColor.color',
                'purchaseOrder' => function ($purchaseOrder) {
                    $purchaseOrder->select(['id','status_id'])->with('status:id,name,code');
                },
            ]);
        }

        return $purchasedThreads->get();
    }

}
