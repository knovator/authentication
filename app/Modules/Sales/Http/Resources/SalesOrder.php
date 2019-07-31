<?php

namespace App\Modules\Sales\Http\Resources;

use App\Modules\Design\Http\Resources\Design as DesignResource;
use App\Modules\Customer\Http\Resources\Customer as CustomerResource;
use App\Modules\Design\Http\Resources\DesignBeam as DesignBeamResource;
use App\Modules\Thread\Http\Resources\Master as MasterResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SalesOrder
 * @package App\Modules\Sales\Http\Resources
 */
class SalesOrder extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'                   => $this->id,
            'order_no'             => $this->order_no,
            'cost_per_meter'       => $this->cost_per_meter,
            'order_date'           => $this->order_date,
            'delivery_date'        => $this->delivery_date,
            'customer'             => new CustomerResource($this->whenLoaded('customer')),
            'status'               => new MasterResource($this->whenLoaded('status')),
            'design'               => new DesignResource($this->whenLoaded('design')),
            'designBeam'           => new DesignBeamResource($this->whenLoaded('designBeam')),
            'manufacturingCompany' => new ManufacturingCompany($this->whenLoaded('manufacturingCompany')),
            'orderRecipes'         => SalesOrderRecipe::collection($this->whenLoaded('orderRecipes')),
        ];
    }
}
