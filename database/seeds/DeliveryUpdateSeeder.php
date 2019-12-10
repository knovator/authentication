<?php


use App\Modules\Sales\Models\Delivery;
use App\Modules\Sales\Repositories\DeliveryRepository;
use Illuminate\Database\Seeder;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class DeliveryUpdateSeeder
 */
class DeliveryUpdateSeeder extends Seeder
{

    protected $deliveryRepository;


    /**
     * PurchaseController constructor
     * @param DeliveryRepository $deliveryRepository
     */
    public function __construct(
        DeliveryRepository $deliveryRepository
    ) {
        $this->deliveryRepository = $deliveryRepository;

    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws RepositoryException
     */
    public function run() {
        $deliveries = $this->deliveryRepository->makeModel()->whereMeters(0)
                                               ->with('partialOrders:delivery_id,total_meters')
                                               ->get();
        foreach ($deliveries as $delivery) {
            /** @var Delivery $delivery */
            $delivery->update(['meters' => $delivery->partialOrders->sum('total_meters')]);
        }


    }


}
