<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use \Storage;

class OfficeSupply extends \Wave\OfficeSupply
{

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'price', 'image', 'minOrder', 'maxOrder', 'stock'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    

}
