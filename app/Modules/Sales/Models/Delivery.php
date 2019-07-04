<?php

namespace App\Modules\Sales\Models;


use App\Models\Master;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Delivery
 * @package App\Modules\Sales\Models
 */
class Delivery extends Model
{

    protected $table = 'deliveries';


    protected $fillable = [
        'sales_order_id',
        'delivery_date',
        'status_id'
    ];


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
    public function partialOrders() {
        return $this->hasMany(RecipePartialOrder::class, 'delivery_id', 'id');
    }

}
