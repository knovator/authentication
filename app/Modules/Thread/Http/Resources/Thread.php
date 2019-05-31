<?php

namespace App\Modules\Thread\Http\Resources;

use App\Modules\Thread\Http\Resources\Master as MasterResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id'           => $this->id,
            'name'         => $this->name,
            'company_name' => $this->company_name,
            'denier'       => $this->denier,
            'price'        => $this->price,
            'is_active'    => $this->is_active,
            'type'         => new MasterResource($this->whenLoaded('type')),
            'threadColors' => ThreadColor::collection($this->whenLoaded('threadColors')),
        ];
    }
}
