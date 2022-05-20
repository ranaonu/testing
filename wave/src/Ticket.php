<?php

namespace Wave;

use Wave\Scopes\AgentScope;
use Wave\Traits\Auditable;
use Wave\Notifications\CommentEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes, Auditable;

    public $table = 'tickets';

    // protected $appends = [
    //     'attachments',
    // ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'title',
        'content',
        'status_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'priority_id',
        'category_id',
        'author_name',
        'author_email',
        'assigned_to_user_id',
        'ticket_number',
        'ticket_issue',
        'user_id',
        'notify'
    ];

    public static function boot()
    {
        parent::boot();
        
        Ticket::observe(new \Wave\Observers\TicketActionObserver);
        
        static::addGlobalScope(new AgentScope);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'ticket_id', 'id');
    }

    

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_id')->where('comment_id',0);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function scopeFilterTickets($query)
    {
        $query->when(request()->input('priority'), function($query) {
                $query->whereHas('priority', function($query) {
                    $query->whereId(request()->input('priority'));
                });
            })
            ->when(request()->input('status'), function($query) {
                $query->whereHas('status', function($query) {
                    $query->whereId(request()->input('status'));
                });
            });
    }

    public function sendCommentNotification($comment)
    {
        $users = \App\User::where(function ($q) {
                $q->whereHas('roles', function ($q) {
                    return $q->where('name', 'Agent');
                })
                ->where(function ($q) {
                    $q->whereHas('comments', function ($q) {
                        return $q->whereTicketId($this->id);
                    })
                    ->orWhereHas('tickets', function ($q) {
                        return $q->whereId($this->id);
                    }); 
                });
            })
            ->when(!$comment->user_id && !$this->assigned_to_user_id, function ($q) {
                $q->orWhereHas('roles', function ($q) {
                    return $q->where('name', 'admin');
                });
            })
            ->when($comment->user, function ($q) use ($comment) {
                $q->where('id', '!=', $comment->user_id);
            })
            ->get();
        $notification = new CommentEmailNotification($comment);

        Notification::send($users, $notification);
        if($comment->user_id && $this->author_email)
        {
            Notification::route('mail', $this->author_email)->notify($notification);
        }
    }
}
