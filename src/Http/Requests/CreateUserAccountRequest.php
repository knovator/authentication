<?php

namespace Knovators\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
        $unique = Rule::unique('user_accounts')->where(function ($query) {
            return $query->where('is_verified', true);
        });

        return [
            'email' => 'required_without:phone|email|' . $unique,
            'phone' => 'required_without:email|numeric|' . $unique,
        ];
    }
}
