<?php

namespace App\Modules\Yarn\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class PaymentRequest
 * @package App\Modules\Yarn\Http\Requests
 */
class PaymentRequest extends FormRequest
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
            'challan_no' => 'required|string',
        ];
    }


}
