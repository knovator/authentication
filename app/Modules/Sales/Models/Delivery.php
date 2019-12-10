<?php

namespace App\Modules\Sales\Models;


use App\Models\Master;
use App\Modules\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class Delivery
 * @package App\Modules\Sales\Models
 */
class Delivery extends Model
{

    use HasModelEvent, SoftDeletes;

    protected $table = 'deliveries';


    protected $fillable = [
        'sales_order_id',
        'delivery_no',
        'delivery_date',
        'status_id',
        'bill_no',
        'meters',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    public static function boot() {
        parent::boot();
        static::deleting(function (Delivery $delivery) {
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
        return $this->hasMany(RecipePartialOrder::class, 'delivery_id', 'id');
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
    public function salesOrder() {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id',
            'id');
    }

    /**
     * @return mixed
     */
    public function orderStocks() {
        return $this->hasManyThrough(Stock::class, RecipePartialOrder::class, 'delivery_id',
            'partial_order_id', 'id', 'id')->where('partial_order_type', '=', 'sales_partial');
    }

}
