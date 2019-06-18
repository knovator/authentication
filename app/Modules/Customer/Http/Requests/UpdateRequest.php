<?php

namespace App\Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class UpdateRequest
 * @package App\Modules\Customer\Http\Requests
 */
class UpdateRequest extends FormRequest
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
            'email'      => 'nullable|email',
            'phone'      => 'required|numeric|digits:10',
            'is_active'  => 'required|boolean',
            'gst_no'     => 'required|string',
            'city_name'  => 'required|string|max:60',
            'state_id'   => 'required|integer',
            'address'    => 'required|string',
        ];
    }


}
