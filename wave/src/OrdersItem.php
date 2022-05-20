<?php

namespace Wave;

use Illuminate\Notifications\Notifiable;
use \Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrdersItem extends Pivot
{

    use Notifiable;

    public $table = 'office_supply_order';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'office_supply_id', 'quantity', 'price'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    

}
