<?php

namespace App\Models;

use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Sales\Models\Delivery;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Stock\Models\Stock;
use App\Modules\Thread\Models\Thread;
use App\Modules\Thread\Models\ThreadColor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Knovators\Masters\Models\Master as Model;

/**
 * Class Master
 *
 * @package Knovators\Masters\Models
 */
class Master extends Model
{


    /**
     *  Parent to child has many relationship
     * @return HasMany
     */
    public function threadColors() {
        return $this->hasMany(ThreadColor::class, 'color_id', 'id');
    }


    /**
     *  Parent to child has many relationship
     * @return HasMany
     */
    public function purchaseOrders() {
        return $this->hasMany(PurchaseOrder::class, 'status_id', 'id');
    }

    /**
     *  Parent to child has many relationship
     * @return HasMany
     */
    public function salesOrders() {
        return $this->hasMany(SalesOrder::class, 'status_id', 'id');
    }

    /**
     *  Parent to child has many relationship
     * @return HasMany
     */
    public function stocks() {
        return $this->hasMany(Stock::class, 'status_id', 'id');
    }

    /**
     *  Parent to child has many relationship
     * @return HasMany
     */
    public function deliveries() {
        return $this->hasMany(Delivery::class, 'status_id', 'id');
    }


    /**
     *  Parent to child has many relationship
     * @return HasMany
     */
    public function threadType() {
        return $this->hasMany(Thread::class, 'type_id', 'id');
    }


}
