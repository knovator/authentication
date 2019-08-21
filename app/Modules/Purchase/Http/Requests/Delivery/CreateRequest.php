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
            'delivery_date'                     => 'required|date_format:Y-m-d|after_or_equal:'
                . $this->purchase->order_date,
            'bill_no'                           => 'required|string',
            'orders'                            => 'required|array',
            'orders.*.purchase_order_thread_id' => 'required|integer|exists:purchase_partial_orders,id',
            'orders.*.kg_qty'                   => 'required|numeric',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {

        return [
            'delivery_date.after_or_equal' => 'Delivery date must be an after or equal to order date'
        ];

    }
}
