<?php

namespace App\Modules\Sales\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Thread\Http\Resources\Master as MasterResource;
use App\Modules\Machine\Http\Resources\Machine as MachineResource;

/**
 * Class RecipePartialOrder
 * @package App\Modules\Sales\Http\Resources
 */
class RecipePartialOrder extends JsonResource
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
            'pcs'          => $this->pcs,
            'meters'       => $this->meters,
            'total_meters' => $this->total_meters,
            'status'       => new MasterResource($this->whenLoaded('status')),
            'machine'      => new MachineResource($this->whenLoaded('machine')),
        ];
    }
}
