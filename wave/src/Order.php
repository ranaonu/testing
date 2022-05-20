<?php

namespace Wave;

use Illuminate\Notifications\Notifiable;
use \Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'order_no', 'shipping', 'total_amount', 'payment_status','shipping_amount', 'order_status', 'payment_info'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function products()
    {
        return $this->belongsToMany('Wave\OfficeSupply')->using('Wave\OrdersItem')->withPivot('quantity');
    }

}
