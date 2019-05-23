<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CreateRequest
 * @package App\Modules\User\Http\Requests
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
            'first_name' => 'required|string|max:60',
            'last_name'  => 'required|string|max:60',
            'email'      => 'required|string|email|max:60|unique:users,email,NULL,id,deleted_at,NULL',
            'phone'      => 'required|numeric|digits:10|unique:users,phone,NULL,id,deleted_at,NULL',
            'is_active'  => 'required|boolean',
            'password'   => 'required|string|min:6',
            'role_ids'   => 'required|array',
            'role_ids.*' => 'required|exists:roles,id',
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
            'email.unique'      => 'This email id is already registered, please choose another email id.',
            'phone.unique'      => 'This phone number is already registered, please choose another phone number.',
            'role_ids.required' => 'role should be required.',
        ];
    }
}
