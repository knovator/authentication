<?php


namespace Knovators\Authentication\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserAccount
 * @package Knovators\Authentication\Http\Resources
 */
class UserAccount extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'email'      => $this->email,
            'phone'      => $this->phone,
            'isVerified' => $this->isVerified,
            'default'    => $this->default
        ];
    }
}
