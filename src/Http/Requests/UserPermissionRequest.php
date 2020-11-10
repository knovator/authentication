<?php

namespace Knovators\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class UserPermissionRequest
 * @package Knovators\Authentication\Http\Requests
 */
class UserPermissionRequest extends FormRequest
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
            'routes' => 'required|array',
            'routes.*' => 'required|string|exists:permissions,route_name',
        ];
    }
}
