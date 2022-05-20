<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use \Storage;

class Order extends \Wave\Order
{

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'shipping', 'total_amount', 'payment_status'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    

}
