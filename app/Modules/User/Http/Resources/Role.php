<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Role
 * @package App\Modules\User\Http\Resources
 */
class Role extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'   => $this->id,
            'name' => $this->name
        ];
    }
}
