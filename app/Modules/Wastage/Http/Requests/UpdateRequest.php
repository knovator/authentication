<?php

namespace App\Modules\Wastage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class UpdateRequest
 * @package App\Modules\Wastage\Http\Requests
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
            'total_fiddles'                                      => 'required|integer',
            'design_id'                                          => 'required|exists:designs,id,deleted_at,NULL',
            'fiddle_picks'                                       => 'required|array|size:' . $this->total_fiddles,
            'fiddle_picks.*.pick'                                => 'required|integer|gte:1',
            'fiddle_picks.*.fiddle_no'                           => 'required|integer',
            'beam_id'                                            => 'required|exists:threads_colors,id,deleted_at,NULL',
            'order_recipes'                                      => 'required|array',
            'order_recipes.*.name'                               => 'required|string|max:60',
            'order_recipes.*.total_fiddles'                      => 'required|integer',
            'order_recipes.*.pcs'                                => 'required|integer|gte:1',
            'order_recipes.*.meters'                             => 'required|integer|gte:1',
            'order_recipes.*.total_meters'                       => 'required|integer',
            'order_recipes.*.thread_color_ids'                   => 'required|array|size:' . $this->total_fiddles,
            'order_recipes.*.thread_color_ids.*.thread_color_id' => 'required|integer',
            'order_recipes.*.thread_color_ids.*.fiddle_no'       => 'required|integer',
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
            'fiddle_picks.required'                              => 'Feeders must be required',
            'fiddle_picks.size'                                  => 'Please enter all feeders picks',
            'fiddle_picks.*.pick.required'                       => 'Feeders pick must be required',
            'fiddle_picks.*.pick.gte'                            => "Feeders pick must be greater than or equal to 1.",
            'design_id.required'                                 => 'Design must be required',
            'beam_id.required'                                   => 'Recipe beam must be required',
            'order_recipes.required'                             => 'At least one recipe must be required',
            'order_recipes.*.name.required'                      => 'Recipe name must be required',
            'order_recipes.*.total_fiddles.required'             => 'Recipe feeder must be required',
            'order_recipes.*.thread_color_ids.required'          => 'Please fill all the selected feeders.',
            'order_recipes.*.thread_color_ids.size'              => 'Recipe thread must be required',
            'order_recipes.*.thread_color_ids.*.thread_color_id' => 'Please fill all the selected feeders.',
            'order_recipes.*.pcs.required'                       => 'Pcs must be required',
            'order_recipes.*.meters.required'                    => 'Meters must be required',
            'order_recipes.*.pcs.gte'                            => 'Pcs must be greater than or equal to 1.',
            'order_recipes.*.meters.gte'                         => "Meters must be greater than or equal to 1.",
        ];

    }

}
