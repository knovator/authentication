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
        return [
            'id'            => $this->id,
            'delivery_date' => $this->delivery_date,
        ];
    }
}
