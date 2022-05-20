<?php

namespace Wave;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request', 'response', 'user_id', 'tracking_number', 'shipped_from', 'invoice_num', 'shipper_email', 'shipper_phone', 'consignee_phone', 'other_charge'
    ];

}
