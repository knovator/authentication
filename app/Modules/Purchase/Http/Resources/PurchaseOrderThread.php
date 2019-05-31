<?php

namespace App\Modules\Purchase\Http\Resources;

use App\Modules\Thread\Http\Resources\ThreadColor as ThreadColorResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PurchaseOrderThread
 * @package App\Modules\Purchase\Http\Resources
 */
class PurchaseOrderThread extends JsonResource
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
            'kg_qty'      => $this->kg_qty,
            'threadColor' => new ThreadColorResource($this->whenLoaded('threadColor'))
        ];
    }
}
