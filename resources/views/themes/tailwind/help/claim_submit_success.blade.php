@extends('theme::layouts.app')

@section('content')
<section class="about-us" style="padding:30px 100px;;">
    <h1 class="text-4xl max-w-md font-extrabold text-gray-900 sm:mx-auto lg:max-w-none sm:text-center pb-10">Thank you, your claim request has been successfully submitted! Our representative will get in touch with you within 5 business days.<br>
        Your claim number is {{$claim->auth_code}}
    </h1>
    
</section>
@endsection

@section('javascript')
	
@endsection
