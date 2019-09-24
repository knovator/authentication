<?php


use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Repositories\SalesOrderRepository;
use App\Modules\Wastage\Models\WastageOrder;
use App\Modules\Wastage\Repositories\WastageOrderRepository;
use App\Modules\Yarn\Models\YarnOrder;
use App\Modules\Yarn\Repositories\YarnOrderRepository;
use Illuminate\Database\Seeder;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class OrderQuantitySeeder
 */
class OrderQuantitySeeder extends Seeder
{

    protected $salesOrderRepository;

    protected $wastageOrderRepository;

    protected $purchaseOrderRepository;

    protected $yarnOrderRepository;


    /**
     * PurchaseController constructor
     * @param SalesOrderRepository    $salesOrderRepository
     * @param WastageOrderRepository  $wastageOrderRepository
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param YarnOrderRepository     $yarnOrderRepository
     */
    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        WastageOrderRepository $wastageOrderRepository,
        PurchaseOrderRepository $purchaseOrderRepository,
        YarnOrderRepository $yarnOrderRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->wastageOrderRepository = $wastageOrderRepository;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->yarnOrderRepository = $yarnOrderRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws RepositoryException
     */
    public function run() {
        // sales orders
        $salesOrders = $this->salesOrderRepository->makeModel()->whereNull('total_meters')
                                                  ->with('orderRecipes:sales_order_id,total_meters')
                                                  ->get();
        foreach ($salesOrders as $salesOrder) {
            /** @var SalesOrder $salesOrder */
            $salesOrder->update(['total_meters' => $salesOrder->orderRecipes->sum('total_meters')]);
        }

        // wastage orders
        $wastageOrders = $this->wastageOrderRepository->makeModel()->whereNull('total_meters')
                                                      ->with('orderRecipes:wastage_order_id,total_meters')
                                                      ->get();
        foreach ($wastageOrders as $wastageOrder) {
            /** @var WastageOrder $wastageOrder */
            $wastageOrder->update(['total_meters' => $wastageOrder->orderRecipes->sum('total_meters')]);
        }

        // purchase orders
        $purchaseOrders = $this->purchaseOrderRepository->makeModel()->whereNull('total_kg')
                                                        ->with('threads:purchase_order_id,kg_qty')
                                                        ->get();
        foreach ($purchaseOrders as $purchaseOrder) {
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder->update(['total_kg' => $purchaseOrder->threads->sum('kg_qty')]);
        }


        // yarn orders
        $yarnOrders = $this->yarnOrderRepository->makeModel()->whereNull('total_kg')
                                                ->with('threads:yarn_order_id,kg_qty')
                                                ->get();
        foreach ($yarnOrders as $yarnOrder) {
            /** @var YarnOrder $yarnOrder */
            $yarnOrder->update(['total_kg' => $yarnOrder->threads->sum('kg_qty')]);
        }
    }


}
