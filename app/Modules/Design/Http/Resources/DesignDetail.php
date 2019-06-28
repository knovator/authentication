<?php

namespace App\Modules\Design\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DesignDetail
 * @package App\Modules\User\Http\Resources
 */
class DesignDetail extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'designer_no'      => $this->designer_no,
            'creming'          => $this->creming,
            'avg_pick'         => $this->avg_pick,
            'pick_on_loom'     => $this->pick_on_loom,
            'panno'            => $this->panno,
            'additional_panno' => $this->additional_panno,
            'reed'             => $this->reed,
        ];
    }
}
