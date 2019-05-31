<?php

namespace App\Modules\Purchase\Models;

use App\Modules\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PurchaseOrderThread
 * @package App\Modules\Purchase\Models
 */
class PurchaseOrderThread extends Model
{

    public $timestamps = false;

    use SoftDeletes;

    protected $table = 'purchase_order_threads';

    protected $fillable = [
        'thread_color_id',
        'purchase_order_id',
        'kg_qty'
    ];


    protected $hidden = [
        'deleted_at',
    ];




}
