<?php

namespace App\Modules\Design\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knovators\Media\Http\Resources\Media;

/**
 * Class DesignImage
 * @package App\Modules\User\Http\Resources
 */
class DesignImage extends JsonResource
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
            'type' => $this->type,
            'file' => new Media($this->whenLoaded('file'))
        ];
    }
}
