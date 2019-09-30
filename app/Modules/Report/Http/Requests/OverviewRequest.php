<?php

namespace App\Modules\Report\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class OverviewRequest
 * @package App\Modules\Report\Http\Requests
 */
class OverviewRequest extends FormRequest
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
            'group'                 => 'required|string|in:daily,monthly,weekly,yearly',
            'date_range'            => 'required|array',
            'date_range.start_date' => 'required|date_format:Y-m-d',
            'date_range.end_date'   => 'required|date_format:Y-m-d'
        ];
    }


}
