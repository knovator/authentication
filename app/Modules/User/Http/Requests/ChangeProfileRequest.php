<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class ChangeProfileRequest
 * @package App\Modules\User\Http\Requests
 */
class ChangeProfileRequest extends FormRequest
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
            'first_name' => 'required|string|max:60',
            'last_name'  => 'required|string|max:60',
            'email'      => 'required|string|unique:users,email,' .
                $this->user->id . ',id,deleted_at,NULL',
            'phone'      => 'required|numeric|digits:10|unique:users,phone,' .
                $this->user->id . ',id,deleted_at,NULL',
            'image_id'   => 'nullable|exists:files,id',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {
        return [
            'email.unique' => 'This email id is already registered, please choose another email id.',
            'phone.unique' => 'This phone number is already registered, please choose another phone number.',
        ];
    }
}
