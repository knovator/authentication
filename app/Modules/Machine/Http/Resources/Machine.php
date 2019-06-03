<?php

namespace App\Modules\Machine\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Design
 * @package App\Modules\User\Http\Resources
 */
class Machine extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'reed'            => $this->reed,
            'thread_color_id' => $this->thread_color_id,
            'panno'           => $this->panno,
            'is_active'       => $this->is_active,
        ];
    }
}
