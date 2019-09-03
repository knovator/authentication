<?php

namespace App\Modules\Wastage\Models;

use App\Modules\Stock\Models\Stock;
use App\Modules\Thread\Models\ThreadColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class WastageOrder
 * @package App\Modules\Wastage\Models
 */
class WastageOrder extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'wastage_orders';

    protected $fillable = [
        'order_no',
        'order_date',
        'delivery_date',
        'challan_no',
        'total_fiddles',
        'beam_id',
        'status_id',
        'customer_id',
        'customer_po_number',
        'manufacturing_company_id',
        'created_by',
        'deleted_by',
        'cost_per_meter',
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
    public function fiddlePicks() {
        return $this->hasMany(WastageFiddle::class, 'wastage_order_id', 'id')->orderBy('fiddle_no');
    }

    /**
     * @return mixed
     */
    public function orderRecipes() {
        return $this->hasMany(WastageOrderRecipe::class, 'wastage_order_id', 'id');
    }

    /**
     * @return mixed
     */
    public function beam() {
        return $this->belongsTo(ThreadColor::class, 'beam_id', 'id');
    }

    /**
     * @return mixed
     */
    public function orderStocks() {
        return $this->morphMany(Stock::class, 'order', 'order_type', 'order_id', 'id');
    }
}
