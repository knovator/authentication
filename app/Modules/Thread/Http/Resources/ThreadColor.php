<?php

namespace App\Modules\Thread\Http\Resources;

use App\Modules\Thread\Http\Resources\Master as MasterResource;
use App\Modules\Thread\Http\Resources\Thread as ThreadResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class User
 * @package App\Modules\User\Http\Resources
 */
class ThreadColor extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'     => $this->id,
            $this->mergeWhen(isset($this->updatable), [
                'updatable' => $this->updatable
            ]),
            'color'  => new MasterResource($this->whenLoaded('color')),
            'thread' => new ThreadResource($this->whenLoaded('thread')),
        ];
    }
}
