<?php

namespace App\Modules\Recipe\Http\Resources;

use App\Modules\Thread\Http\Resources\ThreadColor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class User
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
            'total_fiddles' => $this->total_fiddles,
            'is_active'     => $this->is_active,
            $this->mergeWhen(isset($this->editable), [
                'editable' => $this->editable
            ]),
            'fiddles'       => ThreadColor::collection($this->whenLoaded('fiddles')),
        ];
    }
}
