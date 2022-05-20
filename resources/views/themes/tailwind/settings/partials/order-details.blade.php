<div class="p-8">
    @if($order)
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
                
            </tr>
        </thead>
        <tbody>
            
                <tr>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-gray-900 whitespace-no-wrap">
                        {{ $order->order_no }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                        {{ ($order->payment_status === 0)?'Pending':'Completed' }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                        ${{ $order->total_amount }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                        {{ $order->order_status }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                        {{ date('d-M-Y',strtotime($order->created_at)) }}
                    </td>
                    
                </tr>
        </tbody>
    </table>
    <div class="container max-w-7xl">  
        <h2 class="text-center"> 
            Products
            
        </h2>
        <table class="min-w-full overflow-hidden divide-y divide-gray-200 rounded-lg">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                ?>
        @foreach($products as $c)
                <?php
                $subtotal = $c['price'] * $c['quantity'];
                $total = $total + $subtotal;
                ?>
                <tr>
                    <td class="product-col text-center">
                        <img style="display: inline;" src="{{env('APP_URL').'/storage/'.$c['image']}}" alt="Product image" width="100px">
                    </td>
                    <td class="product-col text-center">
                        <h3 class="product-title">
                            {{$c['name']}}
                        </h3><!-- End .product-title -->
                    </td>
                    <td class="product-col text-center">${{number_format($c['price'],2,'.','')}}</td>
                    <td class="product-col text-center">
                        <div class="cart-product-quantity">
                            <span>{{$c['quantity']}}</span>
                        </div><!-- End .cart-product-quantity -->
                    </td>
                    <td class="product-col text-center">${{number_format($subtotal,2,'.','')}}</td>
                </tr>
                
            
        
        @endforeach
        <tr>
            <td colspan="4" class="product-col text-center">
                <b>Total</b>
            </td>     
            <td class="product-col text-center">${{number_format($total,2,'.','')}}</td>
        </tr>
        </tbody>
    </table><!-- End .table table-wishlist -->  
    </div>

    @else
        <p>Sorry, you don't have a active card .</p>
    @endif
        

</div>
@section('javascript')
<script>

</script>
@endsection