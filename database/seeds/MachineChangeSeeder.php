<?php

use App\Modules\Machine\Repositories\MachineRepository;
use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Sales\Repositories\RecipePartialRepository;
use App\Repositories\MasterRepository;
use Illuminate\Database\Seeder;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class MachineChangeSeeder
 */
class MachineChangeSeeder extends Seeder
{

    protected $recipePartialRepository;

    protected $machineRepository;

    protected $masterRepository;


    /**
     * PurchaseController constructor
     * @param RecipePartialRepository $recipePartialRepository
     * @param MachineRepository       $machineRepository
     * @param MasterRepository        $masterRepository
     */
    public function __construct(
        RecipePartialRepository $recipePartialRepository,
        MachineRepository $machineRepository,
        MasterRepository $masterRepository
    ) {
        $this->recipePartialRepository = $recipePartialRepository;
        $this->machineRepository = $machineRepository;
        $this->masterRepository = $masterRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws RepositoryException
     */
    public function run() {
        $partialOrders = $this->recipePartialRepository->makeModel()->doesntHave('');
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

}
