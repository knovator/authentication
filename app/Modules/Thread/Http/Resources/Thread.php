<?php

namespace App\Modules\Thread\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knovators\Masters\Http\Resources\Master as MasterResource;

/**
 * Class User
 * @package App\Modules\User\Http\Resources
 */
class Thread extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'denier'    => $this->denier,
            'price'     => $this->price,
            'is_active' => $this->is_active,
            'type'      => new MasterResource($this->whenLoaded('type')),
            'colors'    => MasterResource::collection($this->whenLoaded('colors')),
        ];
    }
}
