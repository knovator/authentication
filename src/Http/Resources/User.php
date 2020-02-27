<?php

namespace Knovators\Authentication\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knovators\Media\Http\Resources\Media as MediaResource;

/**
 * Class User
 * @package Knovators\Authentication\Http\Resources
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
            'full_name'  => $this->full_name,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'is_active'  => $this->is_active,
            'image'      => new MediaResource($this->whenLoaded('image')),

            $this->mergeWhen(isset($this->new_token), [
                'token' => $this->new_token
            ]),
            $this->mergeWhen(isset($this->role), [
                'role' => $this->role
            ]),

            $this->mergeWhen($this->permissions, [
                'permissions' => $this->permissions
            ]),


        ];
    }
}
