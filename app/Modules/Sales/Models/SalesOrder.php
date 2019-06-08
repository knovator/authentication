<?php

namespace App\Modules\Sales\Models;

use App\Models\Master;
use App\Modules\Design\Models\DesignBeam;
use App\Modules\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class SalesOrder
 * @package App\Modules\Sales\Models
 */
class SalesOrder extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'sales_orders';

    protected $fillable = [
        'order_no',
        'order_date',
        'delivery_date',
        'cost_per_meter',
        'design_id',
        'design_beam_id',
        'customer_id',
        'status_id',
        'created_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at',
    ];

    public static function boot() {
        parent::boot();
        self::creatingEvent();
        static::deleting(function (SalesOrder $salesOrder) {
            $salesOrder->orderRecipes->each(function (SalesOrderRecipe $orderRecipe) {
                $orderRecipe->partialOrders()->delete();
            });
            $salesOrder->orderRecipes()->delete();
            $salesOrder->orderStocks()->delete();
        });
        self::deletedEvent();
    }


    /**
     * @return mixed
     */
    public function orderRecipes() {
        return $this->hasMany(SalesOrderRecipe::class, 'sales_order_id',
            'id');
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
    public function designBeam() {
        return $this->belongsTo(DesignBeam::class, 'design_beam_id',
            'id');
    }

    /**
     * @return mixed
     */
    public function orderStocks() {
        return $this->morphMany(Stock::class, 'order');
    }

}
