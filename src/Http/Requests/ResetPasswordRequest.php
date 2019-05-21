<?php

namespace Knovators\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ResetPasswordRequest
 * @package Knovators\Authentication\Http\Requests
 */
class ResetPasswordRequest extends FormRequest
{

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
            'token'    => 'required',
            'email'    => 'required|exists:users,email',
            'password' => 'required|min:6',
        ];
    }
}
