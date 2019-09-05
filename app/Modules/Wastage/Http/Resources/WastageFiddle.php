<?php

namespace App\Modules\Wastage\Http\Resources;

use App\Modules\Customer\Http\Resources\Customer as CustomerResource;
use App\Modules\Design\Http\Resources\Design as DesignResource;
use App\Modules\Sales\Models\ManufacturingCompany;
use App\Modules\Thread\Http\Resources\Master as MasterResource;
use App\Modules\Thread\Http\Resources\ThreadColor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WastageFiddle
 * @package App\Modules\Wastage\Http\Resources
 */
class WastageFiddle extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'        => $this->id,
            'pick'      => $this->pick,
            'fiddle_no' => $this->fiddle_no,
        ];
    }
}
