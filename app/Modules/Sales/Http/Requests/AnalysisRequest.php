<?php

namespace App\Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class AnalysisRequest
 * @package App\Modules\Sales\Http\Requests
 */
class AnalysisRequest extends FormRequest
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
            'reports'                   => 'required|array',
            'reports.*.thread_color_id' => 'required|integer',
            'reports.*.total_kg'        => 'required|numeric',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {

        return [
            'reports.required'                   => 'Please select at least one recipe.',
            'reports.*.thread_color_id.required' => 'thread must be required.',
            'reports.*.total_kg.required'        => 'total meters must be required.',
        ];

    }
}
