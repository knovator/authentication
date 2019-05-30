<?php

namespace App\Modules\Sales\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class SalesOrderQuantity
 * @package App\Modules\Sales\Models
 */
class SalesOrderQuantity extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'sales_orders_quantities';

    protected $fillable = [

    ];

}
