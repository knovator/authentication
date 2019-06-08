<?php

namespace App\Modules\Sales\Http\Requests;

use App\Constants\Master as MasterConstant;
use App\Support\FetchMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
        $currentStatusIds = is_array($currentStatusId) ? $currentStatusId : [$currentStatusId];

        return [
            'required',
            Rule::exists('sales_orders', 'id')->where(function ($query) use ($currentStatusIds) {
                /** @var Builder $query */
                $query->whereIn('status_id', $currentStatusIds);
            }),
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {

        return [
            'sales_order_id.exists' => 'Please select valid status.'
        ];
    }


}
