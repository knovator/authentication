<?php

namespace App\Modules\Purchase\Repositories;

use App\Modules\Sales\Models\PurchaseDelivery;
use Knovators\Support\Traits\BaseRepository;

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


}
