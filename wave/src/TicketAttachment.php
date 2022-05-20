<?php

namespace Wave;


use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{

    public $table = 'ticket_attachments';

    // protected $appends = [
    //     'attachments',
    // ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'ticket_id',
        'filename',
        'comment_id'
    ];

}
