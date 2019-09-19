<?php

use App\Constants\Master;
use App\Modules\Sales\Models\RecipePartialOrder;
use App\Modules\Sales\Repositories\RecipePartialRepository;
use App\Repositories\MasterRepository;
use App\Support\FetchMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class MachineChangeSeeder
 */
class MachineChangeSeeder extends Seeder
{

    use FetchMaster;

    protected $recipePartialRepository;

    protected $masterRepository;


    /**
     * PurchaseController constructor
     * @param RecipePartialRepository $recipePartialRepository
     * @param MasterRepository        $masterRepository
     */
    public function __construct(
        RecipePartialRepository $recipePartialRepository,
        MasterRepository $masterRepository
    ) {
        $this->recipePartialRepository = $recipePartialRepository;
        $this->masterRepository = $masterRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws RepositoryException
     */
    public function run() {
        $statusIds = $this->findMasterByCode([Master::SO_MANUFACTURING, Master::SO_DELIVERED]);
        $partialOrders = $this->recipePartialRepository->makeModel()->newQuery()
                                                       ->whereHas('delivery',
                                                           function ($delivery) use ($statusIds) {
                                                               /** @var Builder $delivery */
                                                               $delivery->whereIn('status_id',
                                                                   $statusIds);
                                                           })->doesntHave('assignedMachine')
                                                       ->with('machine')
                                                       ->with('delivery')
                                                       ->get();
        foreach ($partialOrders as $partialOrder) {
            /** @var RecipePartialOrder $partialOrder */
            $partialOrder->assignedMachine()->create([
                'name'           => $partialOrder->machine->name,
                'reed'           => $partialOrder->machine->reed,
                'panno'          => $partialOrder->machine->panno,
                'machine_id'     => $partialOrder->machine_id,
                'sales_order_id' => $partialOrder->delivery->sales_order_id
            ]);
        }


    }

}
