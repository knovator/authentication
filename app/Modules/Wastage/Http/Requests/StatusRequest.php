<?php

namespace App\Modules\Wastage\Http\Requests;

use App\Constants\Master as MasterConstant;
use App\Support\FetchMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knovators\Support\Traits\APIResponse;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class StatusRequest
 * @package App\Modules\Wastage\Http\Requests
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

            case MasterConstant::WASTAGE_CANCELED:
                $currentStatusId = $this->retrieveMasterId(MasterConstant::WASTAGE_PENDING);

                return $this->customValidation($currentStatusId);

            case MasterConstant::WASTAGE_DELIVERED:
                $currentStatusId = $this->retrieveMasterId(MasterConstant::WASTAGE_PENDING);
                $validation = [
                    'order_date'               => 'required|date_format:Y-m-d',
                    'delivery_date'            => 'required|date_format:Y-m-d|after_or_equal:order_date',
                    'cost_per_meter'           => 'required|numeric',
                    'customer_po_number'       => 'nullable|string',
                    'customer_id'              => 'required|exists:customers,id,deleted_at,NULL',
                    'challan_no'               => 'required|string',
                    'manufacturing_company_id' => 'required',
                ];

                return array_merge($validation, $this->customValidation($currentStatusId));

            default:
                return [
                    'code' => 'required|string|not_in:' . MasterConstant::WASTAGE_PENDING
                ];
        }
    }

    /**
     * @param $currentStatusId
     * @return array
     */
    private function customValidation($currentStatusId) {
        return [
            'wastage_order_id' => $this->oldStatusValidation($currentStatusId)
        ];
    }

    /**
     * @param $currentStatusId
     * @return array
     */
    private function oldStatusValidation($currentStatusId) {
        $currentStatusIds = is_array($currentStatusId) ? $currentStatusId : [$currentStatusId];

        return [
            'required',
            Rule::exists('wastage_orders', 'id')->where(function ($query) use ($currentStatusIds) {
                /** @var Builder $query */
                $query->whereIn('status_id', $currentStatusIds)->whereNull('deleted_at');
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
            'code.not_in'           => 'This Order is in canceled status,you can not change.',
            'sales_order_id.exists' => 'Please select valid status.'
        ];
    }


}
