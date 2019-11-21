<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class PartiallyUpdateRequest
 * @package App\Http\Requests
 */
class PartiallyUpdateRequest extends FormRequest
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
            'is_active'   => 'required_without_all:is_demanded,type|boolean',
            'type'        => 'required_without_all:is_active,is_demanded|string',
            'is_demanded' => 'required_without_all:is_active,type|boolean',
        ];
    }


}
