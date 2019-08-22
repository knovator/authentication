<?php

namespace App\Modules\Purchase\Models;


use App\Models\Master;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PurchaseDelivery
 * @package App\Modules\Purchase\Models
 */
class PurchaseDelivery extends Model
{

    protected $table = 'purchase_deliveries';


    protected $fillable = [
        'purchase_order_id',
        'delivery_no',
        'delivery_date',
        'status_id',
        'bill_no',
        'total_kg',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return mixed
     */
    public function partialOrders() {
        return $this->hasMany(PurchasePartialOrder::class, 'delivery_id', 'id');
    }

    /**
     * @return mixed
     */
    public function status() {
        return $this->belongsTo(Master::class, 'status_id',
            'id');
    }

    /**
     * @return mixed
     */
//    public function orderStocks() {
//        return $this->hasManyThrough(Stock::class, RecipePartialOrder::class, 'delivery_id',
//            'partial_order_id', 'id', 'id');
//    }

}
