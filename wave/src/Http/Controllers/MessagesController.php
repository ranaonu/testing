<?php

namespace Wave\Http\Controllers;

use Wave\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Jenssegers\Agent\Agent;
use Lexx\ChatMessenger\Models\Message;
use Lexx\ChatMessenger\Models\Participant;
use Lexx\ChatMessenger\Models\Thread;
use Messenger;
use Validator;

use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class MessagesController extends VoyagerBaseController
{
    public function index(Request $request)
    {
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        $thread="";


        auth()->user()->load('threads.messages.user');


        $threads = auth()->user()->threads;
        if (request()->has('thread') && ($request->thread != null)) {
            if (request('thread')) {
                $thread = auth()->user()->threads()
                   ->where('chat_threads.id', '=', $request->thread)
                   ->first();

                //Read Thread
                $thread->markAsRead(auth()->user()->id);
            } elseif ($thread == "") {
                abort(404);
            }
        }

        $agent = new Agent();

        if ($agent->isMobile()) {
            
                $view = 'theme::messages.index-mobile';

        } else {
          
              $view = 'theme::messages.index-desktop';

        }

        return view($view, [
            'threads' => $threads,
            'thread' => $thread
        ]);
    }

    public function adminindex(Request $request)
    {
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        $thread="";


        auth()->user()->load('threads.messages.user');


        $threads = Thread::get();
        if (request()->has('thread') && ($request->thread != null)) {
            if (request('thread')) {
                $thread = Thread::where('chat_threads.id', '=', $request->thread)
                   ->first();

                //Read Thread
                $thread->markAsRead(auth()->user()->id);
            } elseif ($thread == "") {
                abort(404);
            }
        }
        $support_agents = User::whereRaw('(SELECT name FROM roles WHERE id = users.role_id) = "Support Agent"')->select('id','name')->get();
        
        //dd($support_agents);

        $view = 'theme::messages.adminindex';

        return view($view, [
            'threads' => $threads,
            'thread' => $thread,
            'support_agents' => $support_agents
        ]);
    }

    public function send(Request $request)
    {
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        $this->validate($request, [
           'message' => 'required'
        ], [
           'message.required' => 'Please input your message'
        ]);

        $thread = Thread::create();

        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => auth()->user()->id,
            'body' => $request->message,
        ]);

        $participant = Participant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => auth()->user()->id,
        ]);
        $participant->last_read = Carbon::now();
        $participant->save();

    
        return redirect(route('messages').'?thread='.$thread->id);

    }

    public function reply(Request $request)
    {
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        $this->validate($request, [
            'message' => 'required'
        ], [
            'message.required' => 'Please input your message'
        ]);

        $message = Message::create([
            'thread_id' => $request->thread_id,
            'user_id' => auth()->user()->id,
            'body' => $request->message,
        ]);

        $participant = Participant::firstOrCreate([
            'thread_id' => $request->thread_id,
            'user_id' => auth()->user()->id,
        ]);
        $participant->last_read = Carbon::now();
        $participant->save();
      
        return redirect(route('messages').'?thread='.$message->thread_id)->withFlashSuccess('Message sent successfully');
    }

    public function replyadmin(Request $request)
    {
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        $this->validate($request, [
            'message' => 'required'
        ], [
            'message.required' => 'Please input your message'
        ]);

        $message = Message::create([
            'thread_id' => $request->thread_id,
            'user_id' => auth()->user()->id,
            'body' => $request->message,
        ]);

        $participant = Participant::firstOrCreate([
            'thread_id' => $request->thread_id,
            'user_id' => auth()->user()->id,
        ]);
        $participant->last_read = Carbon::now();
        $participant->save();
      
        return redirect(route('admin.messages.index').'?thread='.$message->thread_id)->withFlashSuccess('Message sent successfully');
    }

    public function getUnreadMessages(Request $request)
    {
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        $unreadMessageCount = auth()->user()->unreadMessagesCount();
        $unreadThreads = [];
        foreach (auth()->user()->threads as $item) {
            if ($item->userUnreadMessagesCount(auth()->user()->id)) {
                $data = [
                  'thread_id' => $item->id,
                  'message' => str_limit($item->messages()->orderBy('id', 'desc')->first()->body, 35),
                  'unreadMessagesCount' => $item->userUnreadMessagesCount(auth()->user()->id),
                  'title' => $item->participants()->with('user')->where('user_id', '<>', auth()->user()->id)->first()->user->name
                ];
                $unreadThreads[] = $data;
            }
        }
        return ['unreadMessageCount' =>$unreadMessageCount,'threads' => $unreadThreads];
    }

    public function assignAgent(Request $request){
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        $participant = Participant::where('user_id',$request->agent)->where('thread_id',$request->thread)->first();
        if(!isset($participant->id)){
            Participant::create([
                'user_id' => $request->agent,
                'thread_id' => $request->thread,
            ]);
        }
        return redirect(route('admin.messages.index').'?thread='.$request->thread)->withFlashSuccess('Agent Assigned Succesfully!');
    }

    public function closeChat($id){
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        $thread = Thread::where('id',$id)->first();
        $thread->end_date = date('Y-m-d');
        $thread->start_date = date('Y-m-d',strtotime($thread->created_at));
        $thread->save();
        return redirect(route('admin.messages.index').'?thread='.$id)->withFlashSuccess('Chat closed!');
    }
}
