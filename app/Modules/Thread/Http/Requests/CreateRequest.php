<?php

namespace App\Modules\Thread\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CreateRequest
 * @package App\Modules\Thread\Http\Requests
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
            'name'        => 'required|string|max:60',
            'denier'      => 'required|integer',
            'type_id'     => 'nullable|exists:masters,id',
            'price'       => 'required|integer',
            'is_active'   => 'required|boolean',
            'color_ids'   => 'required|array',
            'color_ids.*' => 'required|exists:roles,id',

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
