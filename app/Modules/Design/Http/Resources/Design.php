<?php

namespace App\Modules\Design\Http\Resources;

use App\Modules\Design\Http\Resources\DesignDetail as DesignDetailResource;
use App\Modules\Design\Http\Resources\DesignFiddlePick as DesignFiddlePickResource;
use App\Modules\Design\Http\Resources\DesignImage as DesignImageResource;
use App\Modules\Design\Http\Resources\DesignBeam as DesignBeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knovators\Media\Http\Resources\Media;

/**
 * Class Design
 * @package App\Modules\User\Http\Resources
 */
class Design extends JsonResource
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
            'design_no'    => $this->design_no,
            'quality_name' => $this->quality_name,
            'type'         => $this->type,
            'fiddles'      => $this->fiddles,
            'is_active'    => $this->is_active,
            'is_approved'  => $this->is_approved,
            'detail'       => new DesignDetailResource($this->whenLoaded('detail')),
            'fiddlePicks'  => DesignFiddlePickResource::collection($this->whenLoaded('fiddlePicks')),
            'images'       => DesignImageResource::collection($this->whenLoaded('images')),
            'main_image'    => new DesignImageResource($this->whenLoaded('mainImage')),
            'beams'        => DesignBeamResource::collection($this->whenLoaded('beams')),
        ];
    }
}
