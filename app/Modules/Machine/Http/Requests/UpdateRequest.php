<?php

namespace App\Modules\Machine\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class UpdateRequest
 * @package App\Modules\Machine\Http\Requests
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
            'name'            => 'required|string|max:60',
            'reed'            => 'required|string|max:60',
            'thread_color_id' => 'required|integer',
            'is_active'       => 'required|boolean',
            'panno'           => 'required|integer'
        ];
    }


}
