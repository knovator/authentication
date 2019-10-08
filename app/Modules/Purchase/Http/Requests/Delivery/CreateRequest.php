<?php

namespace App\Modules\Purchase\Http\Requests\Delivery;

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
            'orders'                            => 'required|array',
            'orders.*.purchase_order_thread_id' => 'required|integer|exists:purchase_order_threads,id,purchase_order_id,' . $this->purchase->id,
            'bill_no'                           => 'required|string',
            'delivery_date'                     => 'required|date_format:Y-m-d|after_or_equal:'
                . $this->purchase->order_date,
            'orders.*.kg_qty'                   => 'required|numeric|gte:1',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {
        return [
            'orders.required'              => 'At least One Thread must be required',
            'delivery_date.after_or_equal' => 'Delivery date must be an after or equal to order date',
            'bill_no.required'             => 'Challan number is required',
            'orders.*.kg_qty.required'     => 'Quantity must be required',
            'orders.*.kg_qty.gte'          => 'Quantity must be greater than or equal to 1',
        ];

    }
}
