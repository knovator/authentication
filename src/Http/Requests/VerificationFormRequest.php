<?php

namespace Knovators\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;


/**
 * Class VerificationFormRequest
/**
 * Class ResetPasswordRequest
 * @package Knovators\Authentication\Http\Requests
 */
class VerificationFormRequest extends FormRequest
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
            'type'  => 'required|string|in:phone,email',
            'phone' => 'required_without:email',
            'email' => 'required_without:phone|email',
            'key'   => 'required_with:email',
            'otp'   => 'required_with:phone',
        ];
    }
}
