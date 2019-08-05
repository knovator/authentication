<?php

namespace App\Modules\Sales\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class ManufacturingCompany
 * @package App\Modules\Sales\Models
 */
class ManufacturingCompany extends Model
{

    protected $table = 'manufacturing_companies';

    protected $fillable = [
        'id',
        'name',
        'address',
        'country',
        'state',
        'state_code',
        'city',
        'pin_code',
        'phone',
        'gst_no',

    ];

    public $timestamps = false;


}
