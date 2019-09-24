<?php

namespace App\Modules\Purchase\Http\Resources;

use App\Constants\Master;
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
        $thread = [
            'id'          => $this->id,
            'kg_qty'      => $this->kg_qty,
            'threadColor' => new ThreadColorResource($this->whenLoaded('threadColor'))
        ];
        /** @var \App\Modules\Purchase\Models\PurchaseOrderThread $this */
        if ($this->relationLoaded('remainingQuantity')) {
            if ($this->purchaseOrder->status->code == Master::PO_DELIVERED) {
                $thread['remaining_kg_qty'] = 0;
            } else {
                $thread['remaining_kg_qty'] = $this->remaining_kg_qty;
            }
        }


        return $thread;

    }
}
