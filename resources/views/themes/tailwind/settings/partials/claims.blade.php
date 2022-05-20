<div class="p-8">
    @if($orders)
    <table class="min-w-full overflow-hidden divide-y divide-gray-200 rounded-lg">
        <thead>
            <tr>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase bg-gray-100">
                    Auth Code
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Status
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Shipment Number
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Claim Issue
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Details
                </th>
                <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                    Last Update on
                </th>
                
            </tr>
        </thead>
        <tbody>
            @if(!empty($claims))
                @foreach($claims as $item)
                    <tr class="@if($loop->index%2 == 0){{ 'bg-gray-50' }}@else{{ 'bg-gray-100' }}@endif">
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-gray-900 whitespace-no-wrap">
                            {{ $item->auth_code }}
                        </td>
                        
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                            {{ $item->status }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                            {{ $item->shipment_number }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                            {{ $item->claim_issue }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                            {{ $item->description }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                            {{ date('d-M-Y',strtotime($item->updated_at)) }}
                        </td>
                        
                    </tr>
                @endforeach
            @else
            <tr class="bg-gray-50">
                <td colspan="7" class="px-6 py-4 text-sm font-medium leading-5 text-center text-gray-900 whitespace-no-wrap">No claims yet.</td>
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