@extends('theme::layouts.app')
@section('content')
    <main class="main">
        <div class="container" style="padding-top:10px;">
            <a href="{{route('wave.officeSupplyCart')}}" style="float:right;" class="inline-flex items-center justify-center px-4 py-2 text-base font-medium leading-6 text-white whitespace-no-wrap transition duration-150 ease-in-out border border-transparent rounded-md bg-wave-500 hover:bg-wave-600 focus:outline-none focus:border-indigo-700 focus:shadow-outline-wave active:bg-wave-700">
            <i class="fas fa-shopping-cart"></i> (<span id="cart_count">{{$cart_count}}</span>)
            </a>
        </div>

            <div class="page-content">
                <div class="container">
                    <nav aria-label="breadcrumb" class="breadcrumb-nav border-0 mb-0">
                        <div class="container2 d-flex align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Home ></a></li>
                                <li class="breadcrumb-item"><a href="{{route('wave.officeSupplies')}}">Office Supply ></a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{$product->name}}</li>
                            </ol>
                        </div><!-- End .container -->
                    </nav><!-- End .breadcrumb-nav -->
                    <div class="product-details-top">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="product-gallery product-gallery-vertical">
                                    <div class="row">
                                        <figure class="product-main-image">
                                            <?php $product_image = str_replace('\\','/',$product->image); ?>
                                            <img id="product-zoom" src="{{env('APP_URL').'/storage/'.$product_image}}" data-zoom-image="{{env('APP_URL').'/storage/'. $product_image }}" alt="product image">

                                            <!-- <a href="#" id="btn-product-gallery" class="btn-product-gallery">
                                                <i class="fas fa-expand-arrows-alt"></i>
                                            </a> -->
                                        </figure><!-- End .product-main-image -->
                                    </div><!-- End .row -->
                                </div><!-- End .product-gallery -->
                            </div><!-- End .col-md-6 -->

                            <div class="col-md-6">
                                <div class="product-details">
                                    <h1 class="product-title">{{$product->name}}</h1><!-- End .product-title -->

                                    <div class="product-price">
                                        ${{number_format($product->price,2,'.','')}}
                                    </div><!-- End .product-price -->

                                    <div class="product-content">
                                        <p>{{$product->description}} </p>
                                    </div><!-- End .product-content -->

                                    <div class="details-filter-row details-row-size">
                                        <label for="qty">Qty:</label>
                                        <div class="product-details-quantity">
                                            <?php
                                            $min = $product->minOrder;
                                            if($product->maxOrder > $product->stock){
                                                $max = $product->stock;
                                            }else{
                                                $max = $product->maxOrder;
                                            }
                                            ?>
                                            <input type="number" id="qty" class="form-control" value="1" min="{{$min}}" max="{{$max}}" step="1" data-decimals="0" required>
                                        </div><!-- End .product-details-quantity -->
                                    </div><!-- End .details-filter-row -->

                                    <div class="product-details-action">
                                        <a href="javascript:void(0);" class="btn-product btn-cart atc"><i class="fas fa-shopping-cart"></i> &nbsp; <span>add to cart</span></a>
                                    </div><!-- End .product-details-action -->
                                </div><!-- End .product-details -->
                            </div><!-- End .col-md-6 -->
                        </div><!-- End .row -->
                    </div><!-- End .product-details-top -->

                </div><!-- End .container -->
            </div><!-- End .page-content -->
        </main><!-- End .main -->
        <script src="{{url('assets/js/jquery.min.js')}}"></script>
        
        <script>
        $('.atc').click(function(){
            quantity = $('#qty').val();
            product_id = '{{$product->id}}';
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
                    $('#cart_count').html(res.cart_count);
                    obj.html('<i class="fas fa-shopping-cart"></i> &nbsp;<span>add to cart</span>');
                },
                error: function(res){
                    obj.html('<i class="fas fa-shopping-cart"></i> &nbsp;<span>add to cart</span>');
                }
            });
        });
        </script>
@endsection