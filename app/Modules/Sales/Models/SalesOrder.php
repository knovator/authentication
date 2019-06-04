<?php

namespace App\Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class SalesOrder
 * @package App\Modules\Sales\Models
 */
class SalesOrder extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'sales_orders';

    protected $fillable = [
        'order_no',
        'order_date',
        'delivery_date',
        'cost_per_meter',
        'design_id',
        'design_beam_id',
        'customer_id',
        'status_id',
        'created_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at',
    ];

}
