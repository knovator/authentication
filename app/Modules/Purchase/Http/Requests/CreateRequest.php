<?php

namespace App\Modules\Purchase\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CreateRequest
 * @package App\Modules\Purchase\Http\Requests
 */
class CreateRequest extends FormRequest
{

    use APIResponse;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'order_date'                                 => 'required|date_format:Y-m-d',
            'customer_id'                                => 'required|exists:customers,id',
            'threads_purchase_details'                   => 'required|array',
            'threads_purchase_details.*.thread_color_id' => 'required|integer',
            'threads_purchase_details.*.kg_qty'          => 'required|integer',
        ];
    }


}
