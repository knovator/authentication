<?php

use App\Constants\Master;
use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Modules\Stock\Repositories\StockRepository;
use App\Repositories\MasterRepository;
use Illuminate\Database\Seeder;

/**
 * Class PurchaseOrderIssueSeeder
 */
class PurchaseOrderIssueSeeder extends Seeder
{

    protected $purchaseOrderRepository;

    protected $masterRepository;

    protected $stockRepository;

    /**
     * PurchaseController constructor
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param MasterRepository        $masterRepository
     * @param StockRepository         $stockRepository
     */
    public function __construct(
        PurchaseOrderRepository $purchaseOrderRepository,
        MasterRepository $masterRepository,
        StockRepository $stockRepository
    ) {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->masterRepository = $masterRepository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function run() {
        $purchaseOrders = $this->purchaseOrderRepository->with('threads')->all();
        foreach ($purchaseOrders as $purchaseOrder) {
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder->orderStocks()->delete();
            $this->storeStockOrders($purchaseOrder);
        }
        $removedPurchases = $this->purchaseOrderRepository->makeModel()
                                                          ->onlyTrashed()
                                                          ->pluck('id')->toArray();
        $this->stockRepository->makeModel()->whereIn('order_id', $removedPurchases)
                              ->where('order_type', 'purchase')->delete();

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
