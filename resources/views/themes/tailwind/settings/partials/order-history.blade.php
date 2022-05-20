<div class="p-8">
    @if($orders)
    <table class="min-w-full overflow-hidden divide-y divide-gray-200 rounded-lg">
        <thead>
            <tr>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase bg-gray-100">
                    Order No.
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Payment Status
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Total Amount
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Order Status
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Placed on
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Action
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $item)
                <tr class="@if($loop->index%2 == 0){{ 'bg-gray-50' }}@else{{ 'bg-gray-100' }}@endif">
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-gray-900 whitespace-no-wrap">
                        {{ $item->order_no }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                        {{ ($item->payment_status === 0)?'Pending':'Completed' }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                        ${{ $item->total_amount }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                        {{ $item->order_status }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                        {{ date('d-M-Y',strtotime($item->created_at)) }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right whitespace-no-wrap">
                        <a href="{{route('wave.order-details',['id'=>$item->id])}}" class="mr-2 text-indigo-600 hover:underline focus:outline-none" >
                            Details
                        </a>
                    </td>
                </tr>
            @endforeach
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