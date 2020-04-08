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
            'type'     => 'required|string|in:phone,email',
            'otp'      => 'required_with:phone|numeric',
            'token'    => 'required_with:email',
            'email'    => 'required_without:phone|exists:user_accounts,email',
            'phone'    => 'required_without:email|exists:user_accounts,phone',
            'password' => 'required|min:6',
        ];
    }
}
