<?php

namespace App\Modules\Wastage\Http\Resources;

use App\Modules\Customer\Http\Resources\Customer as CustomerResource;
use App\Modules\Design\Http\Resources\Design as DesignResource;
use App\Modules\Sales\Http\Resources\ManufacturingCompany;
use App\Modules\Thread\Http\Resources\Master as MasterResource;
use App\Modules\Thread\Http\Resources\ThreadColor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WastageOrder
 * @package App\Modules\Wastage\Http\Resources
 */
class WastageOrder extends JsonResource
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
            'order_date'           => $this->order_date,
            'delivery_date'        => $this->delivery_date,
            'challan_no'           => $this->challan_no,
            'total_fiddles'        => $this->total_fiddles,
            'customer_po_number'   => $this->customer_po_number,
            'cost_per_meter'       => $this->cost_per_meter,
            'beam'                 => new ThreadColor($this->whenLoaded('beam')),
            'status'               => new MasterResource($this->whenLoaded('status')),
            'customer'             => new CustomerResource($this->whenLoaded('customer')),
            'design'               => new DesignResource($this->whenLoaded('design')),
            'manufacturingCompany' => new ManufacturingCompany($this->whenLoaded('manufacturingCompany')),
            'fiddlePicks'          => WastageFiddle::collection($this->whenLoaded('fiddlePicks')),
            'orderRecipes'         => WastageOrderRecipe::collection($this->whenLoaded('orderRecipes')),
        ];
    }
}
