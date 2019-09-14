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
            'order_date'                                         => 'required|date_format:Y-m-d',
            'delivery_date'                                      => 'required|date_format:Y-m-d|after_or_equal:order_date',
            'cost_per_meter'                                     => 'required|numeric',
            'customer_po_number'                                 => 'nullable|string',
            'customer_id'                                        => 'required|exists:customers,id',
            'design_id'                                          => 'required|exists:designs,id',
            'design_beam_id'                                     => 'required|exists:design_beams,id',
            'manufacturing_company_id'                           => 'required',
            'order_recipes'                                      => 'required|array',
            'order_recipes.*.recipe_id'                          => 'required|integer',
            'order_recipes.*.pcs'                                => 'required|integer',
            'order_recipes.*.meters'                             => 'required|integer',
            'order_recipes.*.total_meters'                       => 'required|integer',
            'order_recipes.*.quantity_details'                   => 'required|array',
            'order_recipes.*.quantity_details.*.thread_color_id' => 'required|integer',
            'order_recipes.*.quantity_details.*.fiddle_no'       => 'required|integer',
            'order_recipes.*.quantity_details.*.denier'          => 'required|integer',
            'order_recipes.*.quantity_details.*.pick'            => 'required|integer',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {

        return [
            'design_id.required'                => 'Design must be required',
            'customer_id.required'              => 'Customer must be required',
            'design_beam_id.required'           => 'Recipe beam must be required',
            'manufacturing_company_id.required' => 'Manufacturing company must be required',
            'order_recipes.required'            => 'At least one recipe must be required',
            'order_recipes.*.pcs.required'      => 'Pcs must be required',
            'order_recipes.*.meters.required'   => 'Meters must be required',
        ];

    }
}
