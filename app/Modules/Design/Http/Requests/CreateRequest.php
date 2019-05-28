<?php

namespace App\Modules\Design\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CreateRequest
 * @package App\Modules\Design\Http\Requests
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
            // design details
            'quality_name'     => 'required|string|max:60',
            'type_id'          => 'required|exists:masters,id',
            'designer_no'      => 'required|string',
            'fiddles'          => 'required|integer',
            'avg_pick'         => 'required|numeric',
            'pick_on_loom'     => 'required|numeric',
            'panno'            => 'required|integer',
            'additional_panno' => 'required|integer',
            'reed'             => 'required|string',
            'is_active'        => 'required|boolean'

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
