<div class="p-8">
                
                    <table class="min-w-full overflow-hidden divide-y divide-gray-200 rounded-lg">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase bg-gray-100">
                                    Ticket number
                                </th>
                                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase bg-gray-100">
                                    Title
                                </th>
                                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase bg-gray-100">
                                    Issue
                                </th>
                                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase bg-gray-100">
                                    Details
                                </th>
                                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase bg-gray-100">
                                    Attachments
                                </th>
                                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase bg-gray-100">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                
                                <td class="px-6 py-4 text-sm font-medium leading-5 text-center text-gray-900 whitespace-no-wrap">
                                    {{ $ticket->ticket_number }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium leading-5 text-center text-gray-900 whitespace-no-wrap">
                                    {{ $ticket->title }}
                                </td>
                            
                                <td class="px-6 py-4 text-sm font-medium leading-5 text-center text-gray-900 whitespace-no-wrap">
                                    {{ $ticket->ticket_issue }}
                                </td>
                            
                                <td class="px-6 py-4 text-sm font-medium leading-5 text-center text-gray-900 whitespace-no-wrap">
                                    {!! $ticket->content !!}
                                </td>
                            
                                <td class="px-6 py-4 text-sm font-medium leading-5 text-center text-gray-900 whitespace-no-wrap">
                                    @foreach($ticket->attachments as $k=>$attachment)
                                        <a target="_blank" href="{{ env('APP_URL').'/storage/'.$attachment->filename }}">Attachment {{ $k+1 }}</a><br>
                                    @endforeach
                                </td>
                            
                                <td class="px-6 py-4 text-sm font-medium leading-5 text-center text-gray-900 whitespace-no-wrap">
                                    {{ $ticket->status->name ?? '' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="form-card">
                        <div class="form-head">
                            <h3>Comments</h3>
                        </div>
                        <div class="form-body">
                            <div class="row">
                            
                                @forelse ($ticket->comments as $comment)
                                    <div class="row mb20">
                                        <div class="col-lg-6">
                                            <p class="font-weight-bold">{{ $comment->author_name }} ({{ $comment->created_at }})</p>
                                            <p>{!! $comment->comment_text !!}</p>
                                            @foreach($comment->attachments as $k=>$attachment)
                                                <a target="_blank" href="{{ env('APP_URL').'/storage/'.$attachment->filename }}"><i class="fas fa-file"></i> Attachment {{ $k+1 }}</a><br>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <hr />
                                    
                                @empty
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <p class="text-center">There are no comments.</p>
                                        </div>
                                    </div>
                                @endforelse

                                <form action="{{ route('tickets.storeComment', $ticket->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group mb20">
                                        <label for="comment_text">Leave a comment</label>
                                        <textarea class="form-control @error('comment_text') is-invalid @enderror" id="comment_text" name="comment_text" rows="3" required style="height:110px"></textarea>
                                        @error('comment_text')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="btn-wrap text-center">
                                        <button type="submit" class="cstm-btn disable_delivery_info">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
            </div>
           
