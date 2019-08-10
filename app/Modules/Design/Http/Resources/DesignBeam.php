<?php

namespace App\Modules\Design\Http\Resources;

use App\Modules\Recipe\Http\Resources\Recipe;
use App\Modules\Thread\Http\Resources\ThreadColor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DesignBeam
 * @package App\Modules\User\Http\Resources
 */
class DesignBeam extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'          => $this->id,
            'threadColor' => new ThreadColor($this->whenLoaded('threadColor')),
            'recipes'     => Recipe::collection($this->whenLoaded('recipes')),
            $this->mergeWhen(isset($this->used_count), [
                'used_count' => $this->used_count
            ]),

        ];
    }
}
