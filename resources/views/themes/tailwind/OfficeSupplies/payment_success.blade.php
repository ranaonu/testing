@extends('theme::layouts.app')

@section('content')
<section class="about-us" style="padding-top:10px;">
    <h1 class="max-w-md text-4xl font-extrabold text-gray-900 sm:mx-auto lg:max-w-none lg:text-5xl sm:text-center pb-10">Thank you, your order has been successfully placed!<br>
        Order No. - {{$order->order_no}}
    </h1>
    <div style="text-align: center">
        <a href="{{route('wave.officeSupplies')}}" class="inline-flex items-center justify-center px-4 py-2 text-base font-medium leading-6 text-white whitespace-no-wrap transition duration-150 ease-in-out border border-transparent rounded-md bg-wave-500 hover:bg-wave-600 focus:outline-none focus:border-indigo-700 focus:shadow-outline-wave active:bg-wave-700"><span>CONTINUE SHOPPING</span><i class="icon-refresh"></i></a>
    </div>
</section>
@endsection

@section('javascript')
	
@endsection
