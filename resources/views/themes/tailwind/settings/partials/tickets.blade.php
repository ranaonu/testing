<div class="p-8">
    @if($orders)
    <table class="min-w-full overflow-hidden divide-y divide-gray-200 rounded-lg">
        <thead>
            <tr>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase bg-gray-100">
                    Ticket Number
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Title
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Ticket Issue
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Status
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Last Update on
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Action
                </th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($tickets))
                @foreach($tickets as $item)
                    <tr class="@if($loop->index%2 == 0){{ 'bg-gray-50' }}@else{{ 'bg-gray-100' }}@endif">
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-gray-900 whitespace-no-wrap">
                            {{ $item->ticket_number }}
                        </td>
                        
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                            {{ $item->title }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                            {{ $item->ticket_issue }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                            {{ $item->status->name }}
                        </td>
                        
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                            {{ date('d-M-Y',strtotime($item->updated_at)) }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right whitespace-no-wrap">
                            <a href="{{route('wave.ticket-details',['id'=>$item->id])}}" class="mr-2 text-indigo-600 hover:underline focus:outline-none" >
                                Details
                            </a>
                        </td>
                    </tr>
                @endforeach
            @else
            <tr class="bg-gray-50">
                <td colspan="7" class="px-6 py-4 text-sm font-medium leading-5 text-center text-gray-900 whitespace-no-wrap">No tickets yet.</td>
            </tr>
            @endif
        </tbody>
    </table>

    @else
        <p>Sorry, you don't have a active card .</p>
    @endif
        

</div>
@section('javascript')
<script>

</script>
@endsection