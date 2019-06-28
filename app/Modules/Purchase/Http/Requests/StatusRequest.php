<?php

namespace App\Modules\Purchase\Http\Requests;

use App\Support\FetchMaster;
use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;
use App\Constants\Master as MasterConstant;

/**
 * Class StatusRequest
 * @package App\Modules\Purchase\Http\Requests
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

            case MasterConstant::PO_PENDING:
                $currentStatusId = $this->retrieveMasterId(MasterConstant::PO_CANCELED);

                return $this->customValidation($currentStatusId);

            case MasterConstant::PO_CANCELED:
                $currentStatusId = $this->retrieveMasterId(MasterConstant::PO_PENDING);

                return $this->customValidation($currentStatusId);
            case MasterConstant::PO_DELIVERED:
                $currentStatusId = $this->retrieveMasterId(MasterConstant::PO_PENDING);

                $validation = [
                    'challan_no' => 'required|string|max:60'
                ];

                return array_merge($validation, $this->customValidation($currentStatusId));

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
            'purchase_order_id' => 'required|exists:purchase_orders,id,status_id,' . $currentStatusId
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {

        return [
            'purchase_order_id.exists' => 'Please select valid status.'
        ];
    }


}
