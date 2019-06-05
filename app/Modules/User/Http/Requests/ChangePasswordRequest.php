<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;


/**
 * Class ChangePasswordRequest
 * @package App\Modules\User\Http\Requests
 */
class ChangePasswordRequest extends FormRequest
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
            'current_password' => 'required|string|min:6',
            'password'         => 'required|string|different:current_password|confirmed|min:6'
        ];
    }

}
