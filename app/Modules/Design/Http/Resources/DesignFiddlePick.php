<?php

namespace App\Modules\Design\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DesignFiddlePick
 * @package App\Modules\User\Http\Resources
 */
class DesignFiddlePick extends JsonResource
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
            'pick'      => $this->pick,
            'fiddle_no' => $this->fiddle_no
        ];
    }
}
