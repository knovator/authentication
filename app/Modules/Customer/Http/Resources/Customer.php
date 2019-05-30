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
            'id'           => $this->id,
            'name'         => $this->name,
            'design_no'    => $this->design_no,
            'quality_name' => $this->quality_name,
            'type'         => $this->type,
            'fiddles'      => $this->fiddles,
            'is_active'    => $this->is_active,
            'is_approved'  => $this->is_approved
        ];
    }
}
