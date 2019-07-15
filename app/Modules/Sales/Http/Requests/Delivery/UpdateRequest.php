<?php

namespace App\Modules\Sales\Http\Requests\Delivery;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class UpdateRequest
 * @package App\Modules\Sales\Http\Requests
 */
class UpdateRequest extends FormRequest
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
            'delivery_date'                  => 'required|date_format:Y-m-d|after_or_equal:'
                . $this->sale->order_date,
            'orders'                         => 'required|array',
            'orders.*.id'                    => 'sometimes|required|integer|exists:recipes_partial_orders,id',
            'orders.*.sales_order_recipe_id' => 'required|integer|exists:sales_orders_recipes,id',
            'orders.*.machine_id'            => 'required|integer',
            'orders.*.meters'                => 'required|integer',
            'orders.*.pcs'                   => 'required|integer',
            'orders.*.total_meters'          => 'required|integer',
            'removed_partial_orders'         => 'sometimes|required|array',

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
