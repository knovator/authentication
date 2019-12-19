<?php

namespace App\Modules\Thread\Models;


use App\Modules\Design\Models\DesignBeam;
use App\Modules\Purchase\Models\PurchaseOrderThread;
use App\Modules\Recipe\Models\Recipe;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Stock\Models\Stock;
use App\Modules\Wastage\Models\WastageOrder;
use App\Modules\Yarn\Models\YarnOrderThread;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Masters\Models\Master;
use App\Constants\Master as MasterConstant;

/**
 * Class ThreadColor
 * @package App\Modules\Thread\Models
 */
class ThreadColor extends Model
{

    use SoftDeletes;

    public $timestamps = false;
    protected $table = 'threads_colors';
    protected $fillable = [
        'color_id',
        'thread_id',
        'is_active',
        'is_demanded',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    /**
     * @return mixed
     */
    public function color() {
        return $this->belongsTo(Master::class, 'color_id', 'id');
    }

    /**
     * @return mixed
     */
    public function thread() {
        return $this->belongsTo(Thread::class, 'thread_id', 'id');
    }


    /**
     * @return mixed
     */
    public function purchaseThreads() {
        return $this->hasMany(PurchaseOrderThread::class, 'thread_color_id', 'id');
    }


    /**
     * @return mixed
     */
    public function designBeams() {
        return $this->hasMany(DesignBeam::class, 'thread_color_id', 'id');
    }


    /**
     * @return mixed
     */
    public function totalOrderMeters() {
        return $this->hasOneThrough(SalesOrder::class, DesignBeam::class, 'thread_color_id',
            'design_beam_id', 'id', 'id')
                    ->selectRaw('SUM(sales_orders.total_meters) as total')->groupBy('thread_color_id');
    }


    /**
     * @return mixed
     */
    public function beamMeters() {
        return $this->hasOneThrough(SalesOrder::class, DesignBeam::class, 'thread_color_id',
            'design_beam_id', 'id', 'id')->groupBy('thread_color_id')
                    ->leftJoin('deliveries', 'deliveries.sales_order_id', '=', 'sales_orders.id');
    }

    /**
     * @return mixed
     */
    public function wastageBeams() {
        return $this->hasMany(WastageOrder::class, 'beam_id', 'id');
    }


    /**
     * @return mixed
     */
    public function yarnOrders() {
        return $this->hasMany(YarnOrderThread::class, 'thread_color_id', 'id');
    }


    /**
     * @return mixed
     */
    public function inPurchaseQty() {
        return $this->stockQty()->whereHas('status', function ($status) {
            /** @var Builder $status */
            $status->where('code', MasterConstant::PO_PENDING);
        });
    }

    /**
     * @return mixed
     */
    public function stockQty() {
        return $this->morphOne(Stock::class, 'product', 'product_type', 'product_id')
                    ->selectRaw('product_id,product_type,sum(kg_qty) as total')
                    ->groupBy(['product_id', 'product_type']);
    }

    /**
     * @return mixed
     */
    public function availableStock() {
        return $this->morphOne(Stock::class, 'product', 'product_type', 'product_id')
                    ->whereHas('status', function ($master) {
                        /** @var Builder $master */
                        $master->whereIn('code', Stock::AVAILABLE_STATUSES);
                    })
                    ->selectRaw('product_id,product_type,sum(kg_qty) as available_qty')
                    ->groupBy(['product_id', 'product_type']);
    }

    /**
     * @return mixed
     */
    public function pendingStock() {
        return $this->stockQty()->whereHas('status', function ($status) {
            /** @var Builder $status */
            $status->where('code', MasterConstant::SO_PENDING);
        });
    }

    /**
     * @return mixed
     */
    public function manufacturingStock() {
        return $this->stockQty()->whereHas('status', function ($status) {
            /** @var Builder $status */
            $status->where('code', MasterConstant::SO_MANUFACTURING);
        });
    }

    /**
     * @return mixed
     */
    public function deliveredStock() {
        return $this->stockQty()->whereHas('status', function ($status) {
            /** @var Builder $status */
            $status->where('code', MasterConstant::SO_DELIVERED);
        });
    }

    /**
     * @return mixed
     */
    public function recipes() {
        return $this->belongsToMany(Recipe::class, 'recipes_fiddles', 'thread_color_id',
            'recipe_id');
    }

    /**
     * @return mixed
     */
    public function stocks() {
        return $this->morphMany(Stock::class, 'product', 'product_type', 'product_id');
    }


}
