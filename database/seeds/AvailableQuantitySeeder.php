<?php

use App\Constants\GenerateNumber;
use App\Constants\Master as MasterConstant;
use App\Modules\Purchase\Repositories\PurchaseOrderRepository;
use App\Modules\Thread\Repositories\ThreadColorRepository;
use App\Repositories\MasterRepository;
use App\Support\UniqueIdGenerator;
use Illuminate\Database\Seeder;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class AvailableQuantitySeeder
 */
class AvailableQuantitySeeder extends Seeder
{

    use UniqueIdGenerator;

    protected $threadColorRepository;

    protected $masterRepository;
    protected $purchaseOrderRepository;


    /**
     * PurchaseController constructor
     * @param ThreadColorRepository   $threadColorRepository
     * @param PurchaseOrderRepository $purchaseOrderRepository
     * @param MasterRepository        $masterRepository
     */
    public function __construct(
        ThreadColorRepository $threadColorRepository,
        PurchaseOrderRepository $purchaseOrderRepository,
        MasterRepository $masterRepository
    ) {
        $this->threadColorRepository = $threadColorRepository;
        $this->masterRepository = $masterRepository;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
    }

    /**
     * Run the database seeds.
     *
     * @throws RepositoryException
     * @throws ValidatorException
     */
    public function run() {
        $threadColors = $this->threadColorRepository->with(['availableStock', 'deliveredStock'])
                                                    ->all();
        $purchasedThreads = [];
        foreach ($threadColors as $threadColor) {
            $purchasedThreads[] = [
                'thread_color_id' => $threadColor->id,
                'kg_qty'          => 500
            ];
        }
        $input['customer_id'] = 22;
        $input['challan_no'] = 'ABC859';
        $input['order_date'] = "2019-10-19";
        $input['total_kg'] = collect($purchasedThreads)->sum('kg_qty');

        try {
            DB::beginTransaction();
            $input['order_no'] = $this->generateUniqueId(GenerateNumber::PURCHASE);
            $input['status_id'] = $this->masterRepository->findByCode(MasterConstant::PO_DELIVERED)->id;
            $purchaseOrder = $this->purchaseOrderRepository->create($input);
            $purchaseOrder->threads()->createMany($purchasedThreads);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
        }

    }

}
