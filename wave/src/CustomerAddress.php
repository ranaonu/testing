<?php

namespace Wave;

use Illuminate\Notifications\Notifiable;
use \Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'company_name','country','address1','city','state','zip','phone','email','type'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    

}
