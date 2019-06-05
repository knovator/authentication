<?php

namespace App\Modules\Machine\Http\Resources;

use App\Modules\Thread\Http\Resources\ThreadColor;
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
            'id'          => $this->id,
            'name'        => $this->name,
            'reed'        => $this->reed,
            'threadColor' => new ThreadColor($this->whenLoaded('threadColor')),
            'panno'       => $this->panno,
            'is_active'   => $this->is_active,
        ];
    }
}
