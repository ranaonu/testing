@extends('theme::layouts.app')

@section('content')
<section class="about-us" style="padding:30px 100px;;">
    <h1 class="text-4xl max-w-md font-extrabold text-gray-900 sm:mx-auto lg:max-w-none sm:text-center pb-10">Thank you, your ticket has been successfully submitted! You can track your tickets <a href="{{route('wave.settings', 'tickets')}}">here.</a><br>
        Your ticket number is {{$ticket->ticket_number}}
    </h1>
    
</section>
@endsection

@section('javascript')
	
@endsection
