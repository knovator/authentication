<?php

namespace App\Modules\SalesOrder\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class SalesOrderQuantity
 * @package App\Modules\SalesOrder\Models
 */
class SalesOrderQuantity extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'sales_orders_quantities';

    protected $fillable = [

    ];

}
