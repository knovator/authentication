<?php

namespace App\Modules\Sales\Http\Requests\Delivery;

use App\Constants\Master as MasterConstant;
use App\Support\FetchMaster;
use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class StatusRequest
 * @package App\Modules\Sales\Http\Requests
 */
class StatusRequest extends FormRequest
{

    use APIResponse, FetchMaster;

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

        switch ($this->code) {

            case MasterConstant::SO_PENDING:
                $currentStatusId = $this->retrieveMasterId(MasterConstant::SO_CANCELED);

                return $this->customValidation($currentStatusId);

            case MasterConstant::SO_CANCELED:
                $currentStatusId = $this->retrieveMasterId(MasterConstant::SO_PENDING);

                return $this->customValidation($currentStatusId);

            case MasterConstant::SO_MANUFACTURING:
                $currentStatusId = $this->retrieveMasterId(MasterConstant::SO_PENDING);

                return $this->customValidation($currentStatusId);

            case MasterConstant::SO_DELIVERED:
                $currentStatusId = $this->retrieveMasterId(MasterConstant::SO_MANUFACTURING);

                return $this->customValidation($currentStatusId);

            default:
                return [
                    'code' => 'required|string'
                ];
        }
    }


    /**
     * @param $currentStatusId
     * @return array
     */
    private function customValidation($currentStatusId) {

        return [
            'delivery_id' => 'required|exists:deliveries,id,status_id,' . $currentStatusId
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {
        return [
            'delivery_id.exists' => 'Please select valid status.'
        ];
    }


}
