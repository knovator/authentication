<?php

namespace App\Modules\Purchase\Http\Resources;

use App\Modules\Customer\Http\Resources\Customer;
use App\Modules\Thread\Http\Resources\Master;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PurchaseOrder
 * @package App\Modules\Purchase\Http\Resources
 */
class PurchaseOrder extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'         => $this->id,
            'order_no'   => $this->order_no,
            'order_date' => $this->order_date,
            'customer'   => new Customer($this->whenLoaded('customer')),
            'status'     => new Master($this->whenLoaded('status')),
            'threads'    => PurchaseOrderThread::collection($this->whenLoaded('threads')),
        ];
    }
}
