<?php

namespace App\Modules\Sales\Models;


use App\Models\Master;
use App\Modules\Purchase\Models\PurchasePartialOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PurchaseDelivery
 * @package App\Modules\Sales\Models
 */
class PurchaseDelivery extends Model
{

    use SoftDeletes;

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
        'deleted_at'
    ];


    public static function boot() {
        parent::boot();
        static::deleting(function (PurchaseDelivery $delivery) {
            $delivery->partialOrders->each(function (RecipePartialOrder $recipePartialOrder) {
                $recipePartialOrder->stocks()->delete();
            });
            $delivery->partialOrders()->delete();
        });
    }

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
