<?php

namespace Wave\Http\Controllers;

use Wave\Ticket;
use Wave\TicketAttachment;
use Wave\Http\Controllers\Traits\MediaUploadingTrait;
use Wave\Notifications\CommentEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Wave\Notifications\TicketEmail;

class TicketController extends \App\Http\Controllers\Controller
{
    use \Wave\Http\Controllers\Traits\MediaUploadingTrait;

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        return view('theme::tickets.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required',
            'content'       => 'required',
            'author_name'   => 'required',
            'author_email'  => 'required|email',
        ]);

        $request->request->add([
            'category_id'   => 1,
            'status_id'     => 1,
            'priority_id'   => 1
        ]);
        
        $request->ticket_number = $this->genTicketNumber();

        $ticket = Ticket::create([
            'title'=>$request->title,
            'content'=>$request->content,
            'author_name'=>$request->author_name,
            'author_email'=>$request->author_email,
            'category_id'=>$request->category_id,
            'status_id'=>$request->status_id,
            'priority_id'=>$request->priority_id,
            'ticket_issue'=>$request->issue,
            'ticket_number'=>$request->ticket_number,
            'user_id'=>auth()->user()->id,
        ]);
        if($request->attachments){
            foreach ($request->attachments as $file) {
                $path = 'ticket-attachments/' . $file;
                Storage::disk(config('voyager.storage.disk'))->put($path, file_get_contents(storage_path('tmp/uploads/' . $file)));
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'filename' => $path,
                    'comment_id'=> 0
                ]);
            }
        }
        Notification::route('mail', 'info@zionshipping.com')->notify(new TicketEmail($ticket));
        return view('theme::tickets.ticket_submit_thanks',['ticket'=>$ticket]);
    }


    public function genTicketNumber(){
        $ticket_number = rand(10000000, 99999999);
        $ticket_al = Ticket::where('ticket_number',$ticket_number)->first();
        if(!empty($ticket_al)){
            $ticket_number = $this->genTicketNumber();
        }
        return $ticket_number;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        $ticket->load('comments');

        return view('tickets.show', compact('ticket'));
    }

    public function storeComment(Request $request, Ticket $ticket)
    {
        $request->validate([
            'comment_text' => 'required'
        ]);

        $comment = $ticket->comments()->create([
            'author_name'   => $ticket->author_name,
            'author_email'  => $ticket->author_email,
            'comment_text'  => $request->comment_text,
            'user_id'  => auth()->user()->id
        ]);

        //$ticket->sendCommentNotification($comment);

        return redirect()->back()->withStatus('Your comment added successfully');
    }
    public function adminStoreComment(Request $request, Ticket $ticket)
    {
        $request->validate([
            'comment_text' => 'required'
        ]);

        $comment = $ticket->comments()->create([
            'author_name'   => auth()->user()->name,
            'author_email'  => auth()->user()->email,
            'comment_text'  => $request->comment_text,
            'user_id'  => auth()->user()->id
        ]);

        //$ticket->sendCommentNotification($comment);
        if($request->attachments){
            foreach ($request->attachments as $file) {
                $path = 'ticket-attachments/' . $file;
                Storage::disk(config('voyager.storage.disk'))->put($path, file_get_contents(storage_path('tmp/uploads/' . $file)));
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'filename' => $path,
                    'comment_id'=> $comment->id
                ]);
            }
        }
        return redirect()->back()->withStatus('Your comment added successfully');
    }
}
