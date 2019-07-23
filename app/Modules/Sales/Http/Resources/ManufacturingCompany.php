<?php

namespace App\Modules\Sales\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ManufacturingCompany
 * @package App\Modules\Sales\Http\Resources
 */
class ManufacturingCompany extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
