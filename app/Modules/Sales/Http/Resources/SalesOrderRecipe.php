<?php

namespace App\Modules\Sales\Http\Resources;

use App\Modules\Recipe\Http\Resources\Recipe as RecipeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SalesOrderRecipe
 * @package App\Modules\Sales\Http\Resources
 */
class SalesOrderRecipe extends JsonResource
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
            'pcs'           => $this->pcs,
            'meters'        => $this->meters,
            'total_meters'  => $this->total_meters,
            'recipe'        => new RecipeResource($this->whenLoaded('recipe')),
            'partialOrders' => RecipePartialOrder::collection($this->whenLoaded('partialOrders')),

        ];
    }
}
