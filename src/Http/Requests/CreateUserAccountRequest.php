<?php

namespace Knovators\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class CreateUserAccountRequest
 * @package Knovators\Authentication\Http\Requests
 */
class CreateUserAccountRequest extends FormRequest
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
            'email' => 'required_without:phone|string|email|max:60|unique:user_accounts,email,null,id,deleted_at,NULL',
            'phone' => 'required_without:email|numeric|digits:10|unique:user_accounts,phone,null,id,deleted_at,NULL',
        ];
    }
}
