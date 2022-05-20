@extends('voyager::master')
<link rel="stylesheet" href="{{url('assets/css/mstyle.css')}}">
    @if(session()->get('display_type') && session()->get('display_type') == "rtl")
        <style>
            .message-box .msg_send_btn{
                right: unset !important;
                left: 0 !important;
            }
        </style>
    @endif
    <style>
        textarea {
            resize: none;
        }
    </style>

@section('content')
<section class="about-us" style="padding-top:10px;">
    <div class="container max-w-7xl">
      
        <div class="row">
              
          <div class="products mb-3">
                <main class="page-content">

                <div class="page-title">
                    <div class="d-flex align-items-center">
                    <span>Chat</span>

                    </div>
                </div>
                @if(request()->has('thread'))
                <div class="form-group" style="float:right;">
                    <select id="agent-dropdown" class="form-control select2 select2-hidden-accessible">
                        <option value="">Select Agent</option>
                        @foreach($support_agents as $agent)
                            <option value="{{$agent->id}}">{{$agent->name}}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary btn-sm edit" id="assign_agent_btn">Assign Agent</button>
                    &nbsp; 
                </div>
                
                @endif
                <div class="main-content">
                    
                <div class="activity-stream-list">
                    
                    <div class="card message-box">

                        <div class="card-body">
                            @if(request()->has('thread'))
                                <div class="form-group" style="float:right;">
                                    <a class="btn btn-primary btn-sm edit" id="close_chat" href="{{route('admin.messages.closeChat',['id'=>$thread->id])}}">Close Chat</a>
                                </div>
                            @endif
                            <div class="messaging">
                                <div class="inbox_msg">
                                    <div class="inbox_people d-md-block d-lg-block ">
                                        <div class="headind_srch">
                                            
                                            <div class="srch_bar @if(!request()->has('thread')) text-left @endif">
                                                <div class="stylish-input-group">
                                                    <input type="text" class="search-bar" id="myInput" placeholder="Search">
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="inbox_chat">
                                            @if($threads->count() > 0)
                                                @foreach($threads as $item)
                                                    @if($item->latestMessage)
                                                        <a class="@if($item->userUnreadMessagesCount(auth()->user()->id)) unread
                                                            @endif" href="{{route('admin.messages.index').'?thread='.$item->id}}">
                                                            <div data-thread="{{$item->id}}"
                                                                class="chat_list @if(($thread != "") && ($thread->id == $item->id))  active_chat @endif" >
                                                                <div class="chat_people">

                                                                    <div class="chat_ib">
                                                                        <h5>
                                                                            @if($item->participants()->with('user')->first() != null)
                                                                                {{ $item->participants()->with('user')->first()->user->name }}
                                                                                @if($item->participants()->count() > 2)
                                                                                + {{ ($item->participants()->count()-2) }} more
                                                                                @endif
                                                                                <span
                                                                                    class="chat_date">{{ $item->messages()->orderBy('id', 'desc')->first()->created_at->diffForHumans() }}</span>
                                                                                @if($item->userUnreadMessagesCount(auth()->user()->id) > 0)
                                                                                    <span class="badge badge-primary mr-5">{{$item->userUnreadMessagesCount(auth()->user()->id)}}</span>
                                                                                @endif
                                                                            @endif
                                                                        </h5>
                                                                        <p>{{ str_limit($item->messages()->orderBy('id', 'desc')->first()->body , 35) }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </a>

                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    @if(request()->has('thread'))
                                       
                                        <form method="post" action="{{route('admin.messages.reply')}}">
                                            @csrf

                                            <input type="hidden" name="thread_id" value="{{isset($thread->id) ? $thread->id : 0}}">
                                            <div class="headind_srch ">
                                                <div class="chat_people box-header">
                                                    <div class="chat_img float-left">
                                                        <img src="{{ env('APP_URL').'/storage/'.$thread->participants()->with('user')->first()->user->avatar }}"
                                                            alt="" height="35px"></div>
                                                    <div class="chat_ib float-left">

                                                        <h5 class="mb-0 d-inline float-left">
                                                            @if($item->participants()->with('user')->first() != null)
                                                                {{ $thread->participants()->with('user')->first()->user->name }}
                                                                @if($thread->participants()->count() > 2)
                                                                    + {{ ($thread->participants()->count()-2) }} more
                                                                @endif
                                                            @endif
                                                        </h5>
                                                        <p class="float-right d-inline mb-0">
                                                            <a class="" href="{{route('messages',['thread'=>$thread->id])}}">
                                                                <i class="icon-refresh font-weight-bold"></i>
                                                            </a>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mesgs">
                                                <div class="msg_history">
                                                    @if(count($thread->messages) > 0 )
                                                        @foreach($thread->messages as $message)
                                                            @if($message->user_id == auth()->user()->id)
                                                                <div class="outgoing_msg">
                                                                    <div class="sent_msg">
                                                                        <p>{{$message->body}}</p>
                                                                        <span class="time_date text-right"> {{\Carbon\Carbon::parse($message->created_at)->format('h:i A | M d Y')}}
                                                                    </span></div>
                                                                </div>
                                                            @else
                                                                <div class="incoming_msg">
                                                                    <div class="incoming_msg_img"><img
                                                                                src="{{ env('APP_URL').'/storage/'.$thread->participants()->with('user')->first()->user->avatar }}"
                                                                                alt=""></div>
                                                                    <div class="received_msg">
                                                                        <div class="received_withd_msg">
                                                                            <p>{{$message->body}}</p>
                                                                            <span class="time_date">{{\Carbon\Carbon::parse($message->created_at)->format('h:i A | M d Y')}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </div>
                                                @if($thread->end_date == null)
                                                <div class="type_msg">
                                                    <div class="input_msg_write">
                                                        <textarea type="text" name="message" class="write_msg"
                                                                placeholder="Type a message"></textarea>
                                                        <button class="msg_send_btn" type="submit">
                                                            <i class="voyager-paper-plane" style="line-height: 2" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                @else
                                                <div class="type_msg">
                                                    <div class="input_msg_write">
                                                        This chat has been closed!
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </form>
                                        

                                    @else
                                        <form method="post" action="{{route('messages.send')}}">
                                            @csrf

                                            <div class="headind_srch bg-dark">
                                                <div class="chat_people header row">
                                                    <div class="col-12 col-lg-3">
                                                        <p class="font-weight-bold text-white mb-0" style="line-height: 35px"></p>
                                                    </div>
                                                    <div class="col-lg-9 col-12 text-dark">
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mesgs">
                                                <div class="msg_history">
                                                    <p class="text-center"><- Select one to start conversing!</p>
                                                </div>
                                                <div class="type_msg">
                                                    <div class="input_msg_write">
                                                        {{--<input type="text" class="write_msg" placeholder="Type a message"/>--}}
                                                        {{-- <textarea type="text" name="message" class="write_msg"
                                                                placeholder="Type a Message"></textarea>
                                                        <button class="msg_send_btn" type="submit">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button> --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </main>
          </div>
        </div>
    </section>
@endsection
@section('javascript')
    <script>

        $(document).ready(function () {
            //Get to the last message in conversation
            $('.msg_history').animate({
                scrollTop: $('.msg_history')[0].scrollHeight
            }, 1000);

            //Read message
            setTimeout(function () {
                var thread = '{{request('thread')}}';
               var message =  $(".inbox_chat").find("[data-thread='" + thread + "']");
                message.parent('a').removeClass('unread');
                message.find('span.badge').remove();
            }, 500 );

            //Filter in conversation
            $("#myInput").on("keyup", function () {
                var value = $(this).val().toLowerCase();
                $(".chat_list").parent('a').filter(function () {
                    $(this).toggle($(this).find('h5,p').text().toLowerCase().trim().indexOf(value) > -1)
                });
            });

            //Route for message notification
            var messageNotificationRoute = '{{ route('messages.unread') }}'
            setInterval(function() {

                $.ajax({
                    type: "POST",
                    url: messageNotificationRoute,
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    datatype: "html",
                    success: function success(data) {
                        if (data.unreadMessageCount > 0) {
                            $('.unreadMessages').empty();
                            $('.mob-notification').removeClass('d-none').html('!');
                            $('.unreadMessageCounter').removeClass('d-none').html(data
                                .unreadMessageCount);
                            var html = "";
                            var host = $(location).attr('protocol') + '//' + $(location).attr(
                                'hostname') + '/user/messages/?thread=';
                            $(data.threads).each(function(key, value) {
                                html += '<a class="dropdown-item" href="' + host + value
                                    .thread_id + '"> ' + '<p class="font-weight-bold mb-0">' +
                                    value.title + ' <span class="badge badge-success">' + value
                                    .unreadMessagesCount + '</span></p>' + '<p class="mb-0">' +
                                    value.message + '</p>' + '</a>';
                            });
                            $('.unreadMessages').html(html);
                            if (window.location.href.indexOf("messages") > -1) {
                                window.location.reload();
                            }
                        } else {
                            $('.unreadMessageCounter').addClass('d-none');
                            $('.mob-notification').addClass('d-none');
                        }
                    }
                });
            }, 5000);
            @if(request()->has('thread'))
            $('#assign_agent_btn').click(function(){
                agent_id = $('#agent-dropdown').val();
                window.location.href = '{{route("admin.messages.assignAgent")}}?agent='+agent_id+'&thread={{$thread->id}}';
            });
            @endif
        });

    </script>
@endsection