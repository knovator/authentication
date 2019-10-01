<?php

namespace App\Modules\Recipe\Http\Resources;

use App\Modules\Thread\Http\Resources\ThreadColor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Design
 * @package App\Modules\User\Http\Resources
 */
class Recipe extends JsonResource
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
            'name'          => $this->name,
            'type'          => $this->type,
            'total_fiddles' => $this->total_fiddles,
            'is_active'     => $this->is_active,
            'fiddles'       => ThreadColor::collection($this->whenLoaded('fiddles')),
            $this->mergeWhen(isset($this->used_count), [
                'used_count' => $this->used_count
            ]),
        ];
    }
}
