<?php

namespace App\Modules\Sales\Models;

use App\Exceptions\UnloadedRelationException;
use App\Models\Master;
use App\Modules\Customer\Models\Customer;
use App\Modules\Design\Models\Design;
use App\Modules\Design\Models\DesignBeam;
use App\Modules\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
        'customer_po_number',
        'design_id',
        'design_beam_id',
        'manufacturing_company_id',
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
    public function recipeMeters() {
        return $this->hasOne(SalesOrderRecipe::class, 'sales_order_id',
            'id')->selectRaw('SUM(total_meters) as total,sales_order_id')
                    ->groupBy('sales_order_id');
    }


    /**
     * @return mixed
     */
    public function totalMeters() {
        return $this->hasOneThrough(RecipePartialOrder::class, Delivery::class, 'sales_order_id',
            'delivery_id', 'id', 'id')
                    ->selectRaw('SUM(total_meters) as total,delivery_id')
                    ->groupBy('delivery_id');
    }


    /**
     * @return mixed
     */
    public function manufacturingTotalMeters() {
        return $this->totalMeters();
    }

    /**
     * @return mixed
     */
    public function deliveredTotalMeters() {
        return $this->totalMeters();
    }


    /**
     * @return mixed
     */
    public function deliveries() {
        return $this->hasMany(Delivery::class, 'sales_order_id', 'id');
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
    public function manufacturingCompany() {
        return $this->belongsTo(ManufacturingCompany::class, 'manufacturing_company_id',
            'id');
    }


    /**
     * @return mixed
     */
    public function design() {
        return $this->belongsTo(Design::class, 'design_id',
            'id');
    }


    /**
     * @return mixed
     */
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id',
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
        return $this->morphMany(Stock::class, 'order', 'order_type', 'order_id', 'id');
    }

    /**
     * @return HasManyThrough
     */
    public function partialOrders() {
        return $this->hasManyThrough(RecipePartialOrder::class, SalesOrderRecipe::class,
            'sales_order_id', 'sales_order_recipe_id', 'id', 'id');
    }


    /**
     * @return UnloadedRelationException
     */
    public function getPendingMetersAttribute() {

        if (!$this->relationLoaded('recipeMeters')) {
            return UnloadedRelationException::make($this, 'recipeMeters');
        }

        if (!$this->relationLoaded('manufacturingTotalMeters')) {
            return UnloadedRelationException::make($this, 'manufacturingTotalMeters');
        }

        if (!$this->relationLoaded('deliveredTotalMeters')) {
            return UnloadedRelationException::make($this, 'deliveredTotalMeters');
        }

        $total = $this->recipeMeters->total;

        if (!is_null($this->manufacturingTotalMeters)) {
            $total = $total - $this->manufacturingTotalMeters->total;
        }

        if (!is_null($this->deliveredTotalMeters)) {
            $total = $total - $this->deliveredTotalMeters->total;
        }

        return $total;
    }


}
