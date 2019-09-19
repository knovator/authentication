<?php

namespace App\Modules\Sales\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Delivery
 * @package App\Modules\Sales\Http\Resources
 */
class Delivery extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {

        $this->partialOrders->map(function ($partialOrder) {
            if (!is_null($partialOrder->assignedMachine)) {
                $partialOrder->machine = $partialOrder->assignedMachine;
                unset($partialOrder->assignedMachine);
            }
        });

        return [
            'id'             => $this->id,
            'bill_no'        => $this->bill_no,
            'delivery_date'  => $this->delivery_date,
            'delivery_no'    => $this->delivery_no,
            'partial_orders' => $this->partialOrders,
            'sales_order_id' => $this->sales_order_id,
            'status'         => $this->status,
            'status_id'      => $this->status_id,
        ];


    }
}
