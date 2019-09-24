<?php

namespace App\Modules\Customer\Http\Requests;

use App\Constants\Order;
use Illuminate\Foundation\Http\FormRequest;
use Knovators\Support\Traits\APIResponse;

/**
 * Class LedgerRequest
 * @package App\Modules\Customer\Http\Requests
 */
class LedgerRequest extends FormRequest
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
            'order_type' => 'required|string|in:' . Order::FABRIC_ORDER . ',' . Order::YARN_ORDER . ','
                . Order::WASTAGE_ORDER . ',' . Order::PURCHASE_ORDER
        ];
    }


}
