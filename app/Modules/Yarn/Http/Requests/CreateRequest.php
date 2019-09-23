<?php

namespace App\Modules\Yarn\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CreateRequest
 * @package App\Modules\Yarn\Http\Requests
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
            'order_date'                => 'required|date_format:Y-m-d',
            'customer_id'               => 'required|exists:customers,id',
            'manufacturing_company_id'  => 'sometimes|required',
            'threads'                   => 'required|array',
            'threads.*.thread_color_id' => 'required|integer',
            'threads.*.kg_qty'          => 'required|numeric',
            'threads.*.rate'            => 'required|numeric',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {
        return [
            'customer_id.required'              => 'Customer must be required',
            'manufacturing_company_id.required' => 'Manufacturing company must be required',
            'threads.required'                  => 'Please select at least one thread.',
            'threads.*.kg_qty.required'         => 'Please fill all selected threads quantity.',
            'threads.*.rate.required'           => 'Please fill all selected threads rate.',
            'threads.*.kg_qty.numeric'          => 'Quantity must be numeric value.',
            'threads.*.rate.numeric'            => 'Rate must be numeric value.',
        ];
    }

}
