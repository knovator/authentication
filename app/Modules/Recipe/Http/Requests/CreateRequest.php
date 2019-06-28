<?php

namespace App\Modules\Recipe\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CreateRequest
 * @package App\Modules\Recipe\Http\Requests
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
            'name'                               => 'required|string|max:60',
            'total_fiddles'                      => 'required|integer',
            'is_active'                          => 'required|boolean',
            'thread_color_ids'                   => 'required|array',
            'thread_color_ids.*.thread_color_id' => 'required|integer',
            'thread_color_ids.*.fiddle_no'       => 'required|integer',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {

        return [
            'thread_color_ids.required'                   => 'Please fill all the selected feeders.',
            'thread_color_ids.*.thread_color_id.required' => 'Please fill all the selected feeders.',
        ];

    }
}
