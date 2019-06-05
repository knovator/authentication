<?php

namespace App\Modules\Thread\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Master
 * @package  App\Modules\Thread\Http\Resources
 */
class Master extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
        ];
    }
}
