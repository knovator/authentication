<?php

namespace App\Modules\Customer\Models;

use App\Models\State;
use App\Modules\SalesOrder\Models\SalesOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;
use Knovators\Support\Traits\HasSlug;

/**
 * Class Customer
 * @package App\Modules\Customer\Models
 */
class Customer extends Model
{

    use SoftDeletes, HasModelEvent, HasSlug;

    protected $table = 'customers';

    protected $slugColumn = 'slug';

    protected $slugifyColumns = ['first_name', 'last_name', 'id'];

    protected $fillable = [
        'first_name',
        'last_name',
        'slug',
        'email',
        'phone',
        'is_active',
        'gst_no',
        'city_name',
        'state_id',
        'address',
    ];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    /**
     * @return mixed
     */
    public function salesOrders() {
        return $this->hasMany(SalesOrder::class, 'customer_id', 'id');
    }


    /**
     * @return mixed
     */
    public function state() {
        return $this->hasMany(State::class, 'state_id', 'id');
    }


}
