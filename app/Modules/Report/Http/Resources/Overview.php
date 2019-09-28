<?php

namespace App\Modules\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Overview
 * @package App\Modules\Report\Http\Resources
 */
class Overview extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            [
                'start_date' => $this->start_date,
                'end_date'   => $this->end_date,
                $this->mergeWhen(isset($this->total_kg), [
                    'total_kg' => $this->total_kg
                ]),
                $this->mergeWhen(isset($this->total_meters), [
                    'total_meters' => $this->total_meters
                ]),
            ],
            $this->total_orders
        ];


    }
}
