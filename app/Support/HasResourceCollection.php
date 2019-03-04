<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;


/**
 * Trait HasResourceCollection
 * @package App\Support
 */
trait HasResourceCollection
{


    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {

        $response = [];
        $response['list'] = $this->collection;
        if ($this->resource instanceof LengthAwarePaginator) {
            $response['pagination'] = [
                'total'        => $this->resource->total(),
                'count'        => $this->resource->count(),
                'per_page'     => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages'  => $this->resource->lastPage()
            ];
        }

        return $response;
    }
}
