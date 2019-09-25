<?php

namespace App\Modules\Dashboard\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TopCustomer
 * @package App\Modules\Dashboard\Http\Resources
 */
class TopCustomer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        $customer = $this->customer->toArray();
        $customer['orders'] = $this->orders;
        return [
            $customer,
            round($this->meters)
        ];
    }
}
