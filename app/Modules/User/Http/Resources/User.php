<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knovators\Media\Http\Resources\Media as MediaResource;
use App\Modules\User\Http\Resources\Role as RoleResource;

/**
 * Class User
 * @package App\Modules\User\Http\Resources
 */
class User extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'is_active'  => $this->is_active,
            'roles'      => RoleResource::collection($this->whenLoaded('roles')),
            'image'      => new MediaResource($this->whenLoaded('image')),
        ];
    }
}
