@extends('theme::layouts.app')
@section('content')
<section class="about-us" style="padding-top:10px;">
    <h1 class="max-w-md text-4xl font-extrabold text-gray-900 sm:mx-auto lg:max-w-none lg:text-5xl sm:text-center pb-10">Cart</h1>
    <div class="container max-w-7xl">
      
      <div class="row">
            
        <div class="products mb-3">
            <div class="page-content">
                <div class="cart">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-9">
                                <form id="cart-form">
                                    @csrf
                                    <table class="table table-cart table-mobile">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
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
                                            @foreach($cart as $c)
                                            <?php
                                            $subtotal = $c['price'] * $c['quantity'];
                                            $total = $total + $subtotal;
                                            ?>
                                            <tr>
                                                <td class="product-col">
                                                    <div class="product">
                                                        <figure class="product-media">
                                                            <a href="{{route('wave.officeSupplyDetail',['id'=>$c['id']])}}">
                                                                <img src="{{env('APP_URL').'/storage/'.$c['image']}}" alt="Product image">
                                                            </a>
                                                        </figure>

                                                        <h3 class="product-title">
                                                            <a href="{{route('wave.officeSupplyDetail',['id'=>$c['id']])}}">{{$c['name']}}</a>
                                                        </h3><!-- End .product-title -->
                                                    </div><!-- End .product -->
                                                </td>
                                                <td class="price-col">${{number_format($c['price'],2,'.','')}}</td>
                                                <td class="quantity-col">
                                                    <div class="cart-product-quantity">
                                                        <input type="number" name="quantity[{{$c['id']}}]" class="form-control" value="{{$c['quantity']}}" min="1" max="10" step="1" data-decimals="0" required>
                                                    </div><!-- End .cart-product-quantity -->
                                                </td>
                                                <td class="total-col">${{number_format($subtotal,2,'.','')}}</td>
                                                <td class="remove-col"><a href="javascript:void(0);" class="btn-remove remove_prod" onclick="remove_prod({{$c['id']}})"><i class="fas fa-close"></i></a></td>
                                            </tr>
                                            @endforeach
                                            
                                        </tbody>
                                    </table><!-- End .table table-wishlist -->
                                </form>
                                <div class="cart-bottom">
                                    
                                    <a href="javascript:void(0);" class="btn btn-outline-dark-2" id="update-cart"><span>UPDATE CART</span><i class="fas fa-refresh"></i></a>
                                </div><!-- End .cart-bottom -->
                            </div><!-- End .col-lg-9 -->
                            <aside class="col-lg-3">
                                <div class="summary summary-cart">
                                    <h3 class="summary-title">Cart Total</h3><!-- End .summary-title -->

                                    <table class="table table-summary">
                                        <tbody>
                                            
                                            <tr class="summary-total">
                                                <td>Total:</td>
                                                <td>${{number_format($total,2,'.','')}}</td>
                                            </tr><!-- End .summary-total -->
                                        </tbody>
                                    </table><!-- End .table table-summary -->

                                    <a href="{{route('wave.officeSupplyCheckout')}}" class="btn btn-outline-primary-2 btn-order btn-block">PROCEED TO CHECKOUT</a>
                                </div><!-- End .summary -->

                                <a href="{{route('wave.officeSupplies')}}" class="btn btn-outline-dark-2 btn-block mb-3"><span>CONTINUE SHOPPING</span><i class="icon-refresh"></i></a>
                            </aside><!-- End .col-lg-3 -->
                        </div><!-- End .row -->
                    </div><!-- End .container -->
                </div><!-- End .cart -->
            </div><!-- End .page-content -->
            
        </div>
       </div>
    </div>
</section>
<script src="{{url('assets/js/jquery.min.js')}}"></script>
<script>
$('#update-cart').click(function(){
    $.ajax({
        type: "POST",
        url: '{{route("wave.updateCart")}}',
        data: $('#cart-form').serialize(),
        dataType: 'JSON',
        beforeSend: function() {
            $(".overlay").show();
        },
        success: function(){
            window.location.reload();
        },
        error: function(){
            $(".overlay").hide();
        }
    });
});
function remove_prod(prod_id){
    prod_id = prod_id;
    $.ajax({
        type: "GET",
        url: '{{url("remove-from-cart")}}/'+prod_id,
        dataType: 'JSON',
        beforeSend: function() {
            $(".overlay").show();
        },
        success: function(){
            window.location.reload();
        },
        error: function(){
            $(".overlay").hide();
        }
    });
}
</script>
@endsection