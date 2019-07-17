<?php

use App\Constants\Master;
use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Repositories\MasterRepository;
use Illuminate\Database\Seeder;

/**
 * Class PurchaseOrderIssueSeeder
 */
class PurchaseOrderIssueSeeder extends Seeder
{

    protected $purchaseOrderRepository;

    protected $masterRepository;

    /**
     * PurchaseController constructor
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param MasterRepository        $masterRepository
     */
    public function __construct(
        PurchaseOrderRepository $purchaseOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->masterRepository = $masterRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $purchaseOrders = $this->purchaseOrderRepository->with('threads')->all();
        foreach ($purchaseOrders as $purchaseOrder) {
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder->orderStocks()->delete();
            $this->storeStockOrders($purchaseOrder);
        }
    }

    /**
     * @param $purchaseOrder
     * @param $input
     */
    private function storeStockOrders(PurchaseOrder $purchaseOrder) {
        $stockItems = [];
        foreach ($purchaseOrder->threads as $key => $purchasedThread) {
            $stockItems[$key] = [
                'product_id'   => $purchasedThread->thread_color_id,
                'product_type' => 'thread_color',
                'kg_qty'       => $purchasedThread->kg_qty,
                'status_id'    => $purchaseOrder->status_id,
            ];
        }
        $purchaseOrder->orderStocks()->createMany($stockItems);
    }
}
