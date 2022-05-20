@extends('theme::layouts.app')
@section('content')
<section class="about-us" style="padding-top:10px;">
    <div class="container">
        <a href="{{route('wave.officeSupplyCart')}}" style="float:right;" class="inline-flex items-center justify-center px-4 py-2 text-base font-medium leading-6 text-white whitespace-no-wrap transition duration-150 ease-in-out border border-transparent rounded-md bg-wave-500 hover:bg-wave-600 focus:outline-none focus:border-indigo-700 focus:shadow-outline-wave active:bg-wave-700">
        <i class="fas fa-shopping-cart"></i> (<span id="cart_count">{{$cart_count}}</span>)
        </a>
    </div>
    <h1 class="max-w-md text-4xl font-extrabold text-gray-900 sm:mx-auto lg:max-w-none lg:text-5xl sm:text-center pb-10">Office Supplies</h1>
    <div class="container max-w-7xl">
      
      <div class="row">
            
        <div class="products mb-3">
            <div class="row justify-content-center">
                @foreach ($products as $product)
                <div class="col-6 col-md-4 col-lg-4 col-xl-3">
                    <div class="product product-7 text-center">
                        <figure class="product-media">
                            {{-- <span class="product-label label-new">New</span> --}}
                            <a href="{{route('wave.officeSupplyDetail',['id'=>$product->id])}}">
                                <img src="{{env('APP_URL').'/storage/'.$product->image}}" alt="Product image" class="product-image">
                            </a>

                            <div class="product-action-vertical">
                                {{-- <a href="#" class="btn-product-icon btn-wishlist btn-expandable"><span>add to wishlist</span></a>
                                <a href="popup/quickView.html" class="btn-product-icon btn-quickview" title="Quick view"><span>Quick view</span></a>
                                <a href="#" class="btn-product-icon btn-compare" title="Compare"><span>Compare</span></a> --}}
                            </div><!-- End .product-action-vertical -->

                            <div class="product-action">
                                <?php
                                $min = $product->minOrder;
                                if($product->maxOrder > $product->stock){
                                    $max = $product->stock;
                                }else{
                                    $max = $product->maxOrder;
                                }
                                ?>
                                <input type="number" class = "os-qty" placeholder="QTY" step="1" min = "{{$min}}" max="{{$max}}" value="{{$min}}" required />
                                <input type="hidden" class = "os-id" value="{{$product->id}}"  />
                                
                                <a href="javascript:void(0);" class="btn-product atc" ><i class="fas fa-shopping-cart"></i> &nbsp;<span>add to cart</span></a>
                            </div><!-- End .product-action -->
                        </figure><!-- End .product-media -->

                        <div class="product-body">
                            {{-- <div class="product-cat">
                                <a href="#">Women</a>
                            </div><!-- End .product-cat --> --}}
                            <h3 class="product-title"><a href="{{route('wave.officeSupplyDetail',['id'=>$product->id])}}">{{$product->name}}</a></h3><!-- End .product-title -->
                            <div class="product-price">
                                ${{number_format($product->price,2,'.','')}}
                            </div><!-- End .product-price -->
                            

                            
                        </div><!-- End .product-body -->
                    </div><!-- End .product -->
                </div><!-- End .col-sm-6 col-lg-4 col-xl-3 -->
                @endforeach
            </div><!-- End .row -->
        </div><!-- End .products -->
        {{$products->links()}}
      </div>
    </div>
  </section>
  <script src="{{url('assets/js/jquery.min.js')}}"></script>
  <script src="{{url('assets/js/main.js')}}"></script>
  <script>
  $('.atc').click(function(){
    quantity = $(this).parent().find('.os-qty').val();
    product_id = $(this).parent().find('.os-id').val();
    token = '{{ csrf_token() }}';
    $(this).html('<i class="fa fa-spinner fa-spin"></i>');
    obj = $(this);
    $.ajax({
        type: "POST",
        url: '{{route("wave.addToCart")}}',
        data: {'_token':token,'quantity':quantity,'product_id':product_id},
        dataType: 'JSON',
        success: function(res){
            if(!res.success){
                alert(res.message);
            }
            obj.html('<i class="fas fa-shopping-cart"></i> &nbsp;<span>add to cart</span>');
            $('#cart_count').html(res.cart_count);
        },
        error: function(res){
            obj.html('<i class="fas fa-shopping-cart"></i> &nbsp;<span>add to cart</span>');
        }
    });
  });
  </script>
@endsection