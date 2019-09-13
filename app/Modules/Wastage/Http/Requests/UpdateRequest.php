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
            'fiddle_picks'                                       => 'required|array',
            'fiddle_picks.*.pick'                                => 'required|integer',
            'fiddle_picks.*.fiddle_no'                           => 'required|integer',
            'beam_id'                                            => 'required|exists:threads_colors,id,deleted_at,NULL',
            'order_recipes'                                      => 'required|array',
            'order_recipes.*.name'                               => 'required|string|max:60',
            'order_recipes.*.total_fiddles'                      => 'required|integer',
            'order_recipes.*.thread_color_ids'                   => 'required|array',
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

        ];

    }

}