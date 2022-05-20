<?php

namespace Wave;

use Illuminate\Notifications\Notifiable;
use \Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeSupply extends Model
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
