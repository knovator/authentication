<?php

namespace App\Modules\Wastage\Http\Resources;

use App\Modules\Recipe\Http\Resources\Recipe as RecipeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WastageOrderRecipe
 * @package App\Modules\Wastage\Http\Resources
 */
class WastageOrderRecipe extends JsonResource
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
            'recipe'       => new RecipeResource($this->whenLoaded('recipe'))
        ];
    }
}