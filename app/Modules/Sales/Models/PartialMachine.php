<?php

namespace App\Modules\Sales\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class PartialMachine
 * @package App\Modules\Sales\Models
 */
class PartialMachine extends Model
{

    public $timestamps = false;

    protected $table = 'partial_order_machines';

    protected $fillable = [
        'id',
        'name',
        'reed',
        'panno',
        'machine_id',
        'partial_order_id',
        'sales_order_id',
    ];


}
