<?php

namespace App\Modules\SalesOrder\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class SalesOrder
 * @package App\Modules\SalesOrder\Models
 */
class SalesOrder extends Model
{

    use SoftDeletes,HasModelEvent;

    protected $table = 'sales_orders';

    protected $fillable = [
        'order_no',
        'order_date',
        'delivery_date',
        'bill_no',
        'design_id',
        'design_beam_id',
        'customer_id',
        'status_id',
        'created_by',
        'deleted_by',
    ];

}
