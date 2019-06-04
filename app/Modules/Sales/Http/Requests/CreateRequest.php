<?php

namespace App\Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CreateRequest
 * @package App\Modules\Sales\Http\Requests
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
            'order_date'     => 'required|date_format:Y-m-d',
            'delivery_date'  => 'required|date_format:Y-m-d',
            'cost_per_meter' => 'required|integer',
            'customer_id'    => 'required|exists:customers,id',
            'design_id'      => 'required|exists:designs,id',
            'design_beam_id' => 'required|exists:design_beams,id'

        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {

        return [
            'thread_color_ids.required' => 'Please fill the fiddle details.',
        ];

    }
}
