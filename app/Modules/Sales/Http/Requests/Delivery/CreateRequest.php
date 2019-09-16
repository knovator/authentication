<?php

namespace App\Modules\Sales\Http\Requests\Delivery;

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
            'delivery_date'                  => 'required|date_format:Y-m-d|after_or_equal:'
                . $this->sale->order_date,
            'orders'                         => 'required|array',
            'orders.*.sales_order_recipe_id' => 'required|integer|exists:sales_orders_recipes,id',
            'orders.*.machine_id'            => 'required|integer',
            'orders.*.meters'                => 'required|integer|min:1',
            'orders.*.pcs'                   => 'required|integer|min:1',
            'orders.*.total_meters'          => 'required|integer',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {

        return [
            'delivery_date.after_or_equal' => 'Delivery date must be an after or equal to order date.',
            'orders.required'              => 'At least one recipe must be required.',
            'orders.*.machine_id'          => 'Machine must be required.',
            'orders.*.meters'              => 'Meters must be required.',
            'orders.*.meters.min'          => "Meters can't be zero.",
            'orders.*.pcs.min'             => 'Pcs must be greater than or equal to 1.',
            'orders.*.pcs'                 => 'Pcs must be required.',
        ];

    }
}
