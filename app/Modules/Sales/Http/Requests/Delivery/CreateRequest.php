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
            'delivery_date'                => 'required|date_format:Y-m-d',
            'order_recipes'                => 'required|array',
            'order_recipes.*.id'           => 'required|integer|exists:sales_orders_recipes,id',
            'order_recipes.*.machine_id'   => 'required|integer',
            'order_recipes.*.meters'       => 'required|integer',
            'order_recipes.*.pcs'          => 'required|integer',
            'order_recipes.*.total_meters' => 'required|integer',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {

        return [


        ];

    }
}
