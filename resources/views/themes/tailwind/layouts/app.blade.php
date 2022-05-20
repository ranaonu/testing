<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>

    @if(isset($seo->title))
        <title>{{ $seo->title }}</title>
    @else
        <title>{{ setting('site.title', 'Laravel Wave') . ' - ' . setting('site.description', 'The Software as a Service Starter Kit built on Laravel & Voyager') }}</title>
    @endif

    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge"> <!-- † -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="url" content="{{ url('/') }}">

    <link rel="icon" href="{{ setting('site.favicon', '/wave/favicon.png') }}" type="image/x-icon">

    {{-- Social Share Open Graph Meta Tags --}}
    @if(isset($seo->title) && isset($seo->description) && isset($seo->image))
        <meta property="og:title" content="{{ $seo->title }}">
        <meta property="og:url" content="{{ Request::url() }}">
        <meta property="og:image" content="{{ $seo->image }}">
        <meta property="og:type" content="@if(isset($seo->type)){{ $seo->type }}@else{{ 'article' }}@endif">
        <meta property="og:description" content="{{ $seo->description }}">
        <meta property="og:site_name" content="{{ setting('site.title') }}">

        <meta itemprop="name" content="{{ $seo->title }}">
        <meta itemprop="description" content="{{ $seo->description }}">
        <meta itemprop="image" content="{{ $seo->image }}">

        @if(isset($seo->image_w) && isset($seo->image_h))
            <meta property="og:image:width" content="{{ $seo->image_w }}">
            <meta property="og:image:height" content="{{ $seo->image_h }}">
        @endif
    @endif

    <meta name="robots" content="index,follow">
    <meta name="googlebot" content="index,follow">

    @if(isset($seo->description))
        <meta name="description" content="{{ $seo->description }}">
    @endif
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"/>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css'>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" type="text/css" href="https://www.jqueryscript.net/demo/jQuery-Plugin-For-Country-Selecter-with-Flags-flagstrap/dist/css/flags.css"> -->
    <!-- Styles -->
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/xcode.min.css" />

    
    <link href="{{ asset('themes/' . $theme->folder . '/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/' . $theme->folder . '/css/custom-style.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/' . $theme->folder . '/css/msdropdown.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/' . $theme->folder . '/css/msdropdown_flag.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/' . $theme->folder . '/css/intlTelInput.css') }}" rel="stylesheet">
    @if(Route::current()->getName() == 'messages')
        <link rel="stylesheet" href="{{url('assets/css/mstyle.css')}}">
    @endif
    @if(Route::current()->getName() == 'wave.officeSupplies' || Route::current()->getName() == 'wave.officeSupplyDetail' || Route::current()->getName() == 'wave.officeSupplyCart' || Route::current()->getName() == 'wave.officeSupplyCheckout')
    <link rel="stylesheet" href="{{url('assets/css/style.css')}}">
    <link rel="stylesheet" href="{{url('assets/css/plugins/magnific-popup/magnific-popup.css')}}">
    @endif
</head>
<body class="flex flex-col min-h-screen @if(Request::is('/')){{ 'bg-white' }}@else{{ 'bg-gray-50' }}@endif @if(config('wave.dev_bar')){{ 'pb-10' }}@endif">
    @if(config('wave.demo') && Request::is('/'))
        @include('theme::partials.demo-header')
    @endif

    @include('theme::partials.header')

    <main class="flex-grow overflow-x-hidden">
        @yield('content')
    </main>



    @include('theme::partials.footer')

    @if(config('wave.dev_bar'))
        @include('theme::partials.dev_bar')
    @endif

    <!-- Full Screen Loader -->
    <div id="fullscreenLoader" class="fixed inset-0 top-0 left-0 z-50 flex flex-col items-center justify-center hidden w-full h-full bg-gray-900 opacity-50">
        <svg class="w-5 h-5 mr-3 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p id="fullscreenLoaderMessage" class="mt-4 text-sm font-medium text-white uppercase"></p>
    </div>
    <!-- End Full Loader -->


    @include('theme::partials.toast')
    @if(session('message'))
        <script>setTimeout(function(){ popToast("{{ session('message_type') }}", "{{ session('message') }}"); }, 10);</script>
    @endif
    @waveCheckout

    @if(Route::current()->getName() == 'wave.officeSupplyDetail' || Route::current()->getName() == 'wave.officeSupplyCart')
    <script src="{{url('assets/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{url('assets/js/jquery.hoverIntent.min.js')}}"></script>
    <script src="{{url('assets/js/jquery.waypoints.min.js')}}"></script>
    <script src="{{url('assets/js/superfish.min.js')}}"></script>
    <script src="{{url('assets/js/owl.carousel.min.js')}}"></script>
    <script src="{{url('assets/js/bootstrap-input-spinner.js')}}"></script>
    <script src="{{url('assets/js/jquery.elevateZoom.min.js')}}"></script>
    <script src="{{url('assets/js/bootstrap-input-spinner.js')}}"></script>
    <script src="{{url('assets/js/jquery.magnific-popup.min.js')}}"></script>
    <script src="{{url('assets/js/main.js')}}"></script>
    @endif
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js" defer data-deferred="1"></script>
    <div class="overlay" style="display:none;">
        <div class="loader"></div>
    </div>
</body>
</html>
