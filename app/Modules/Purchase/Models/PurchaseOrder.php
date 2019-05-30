<?php

namespace App\Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class PurchaseOrder
 * @package App\Modules\Purchase\Models
 */
class PurchaseOrder extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'order_no',
        'order_date',
        'customer_id',
        'status_id',
        'created_by',
        'deleted_by',
    ];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    /**
     * @return mixed
     */
    public function threads() {
        return $this->hasMany(PurchaseOrderThread::class, 'purchase_order_id', 'id');
    }

}
