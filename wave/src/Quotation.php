<?php

namespace Wave;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request', 'dhl_response', 'fedex_response', 'usps_response', 'canadaPost_response', 'user_id', 'shipped', 'shipped_trackingNumber', 'pickup_scheduled', 'pickup_id'
    ];
}
