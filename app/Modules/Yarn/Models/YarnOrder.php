<?php

namespace App\Modules\Yarn\Models;

use App\Models\Master;
use App\Modules\Customer\Models\Customer;
use App\Modules\Sales\Models\ManufacturingCompany;
use App\Modules\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class YarnOrder
 * @package App\Modules\Yarn\Models
 */
class YarnOrder extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'yarn_sales_orders';

    protected $fillable = [
        'order_no',
        'order_date',
        'customer_id',
        'status_id',
        'challan_no',
        'total_kg',
        'created_by',
        'deleted_by',
        'manufacturing_company_id'
    ];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'deleted_at',
        //        'created_at',
        'updated_at'
    ];


    public static function boot() {
        parent::boot();
        self::creatingEvent();
        static::deleting(function (YarnOrder $model) {
            $model->threads()->delete();
            $model->orderStocks()->delete();
        });
        self::deletedEvent();
    }

    /**
     * @return mixed
     */
    public function threads() {
        return $this->hasMany(YarnOrderThread::class, 'yarn_order_id', 'id');
    }

    /**
     * @return mixed
     */
    public function orderStocks() {
        return $this->morphMany(Stock::class, 'order', 'order_type', 'order_id', 'id');
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
    public function threadQty() {
        return $this->hasOne(YarnOrderThread::class, 'yarn_order_id', 'id')
                    ->groupBy('yarn_order_id')
                    ->selectRaw('sum(kg_qty) as total,yarn_order_id');
    }


    /**
     * @return mixed
     */
    public function quantity() {
        return $this->threadQty();
    }


    /**
     * @return mixed
     */
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * @return mixed
     */
    public function status() {
        return $this->belongsTo(Master::class, 'status_id', 'id');
    }


}
