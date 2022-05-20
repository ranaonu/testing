<?php

namespace Wave;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consignee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'consignee_name', 'consignee_phone', 'consignee_homephone', 'consignee_address_country', 'consignee_address_city', 'consignee_address_state', 'consignee_address_zip', 'consignee_address'
    ];
}
