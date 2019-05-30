<?php

namespace App\Modules\Customer\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Customer
 * @package App\Modules\User\Http\Resources
 */
class Customer extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'is_active'  => $this->is_active,
            'gst_no'     => $this->gst_no,
            'address'    => $this->city_name,
            'state'      => $this->whenLoaded('state')
        ];
    }
}
