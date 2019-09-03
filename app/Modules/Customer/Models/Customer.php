<?php

namespace App\Modules\Customer\Models;

use App\Models\State;
use App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Sales\Models\SalesOrder;
use App\Notifications\OrderFormNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Knovators\Support\Traits\HasModelEvent;
use Knovators\Support\Traits\HasSlug;

/**
 * Class Customer
 * @package App\Modules\Customer\Models
 */
class Customer extends Model
{

    use SoftDeletes, HasModelEvent, HasSlug, Notifiable;

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
        'agent_id'
    ];

    protected $appends = ['full_name'];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    /**
     * @return string
     */
    public function getFullNameAttribute() {
        return ucfirst($this->first_name . ' ' . $this->last_name);
    }


    /**
     * @param $salesOrder
     * @param $attachment
     */
    public function sendOrderNotifyMail($companyName, $attachment) {
        $this->notify((new OrderFormNotification($companyName, $attachment))->delay(now()
            ->addSeconds(10)));
    }


    /**
     * @return mixed
     */
    public function salesOrders() {
        return $this->hasMany(SalesOrder::class, 'customer_id', 'id');
    }

    /**
     * @return mixed
     */
    public function purchaseOrders() {
        return $this->hasMany(PurchaseOrder::class, 'customer_id', 'id');
    }


    /**
     * @return mixed
     */
    public function state() {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }


    /**
     * @return mixed
     */
    public function agent() {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }


}
