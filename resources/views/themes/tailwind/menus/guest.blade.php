<nav class="flex items-center justify-end flex-1 hidden w-full h-full space-x-10 md:flex vc-nav">
    <div class="relative h-full select-none">
        <div class="inline-flex items-center h-full space-x-2 text-base font-medium leading-6 text-gray-500 transition duration-150 ease-in-out cursor-pointer select-none hover:text-wave-600 focus:outline-none focus:text-wave-500">
            <a href="/">{{ __('menu.Home') }}</a>
        </div>
    </div>
    <div @mouseenter="dropdown = true" @mouseleave="dropdown=false" @click.away="dropdown=false" x-data="{ dropdown: false }" class="relative h-full select-none">
        <div :class="{ 'text-wave-600': dropdown, 'text-gray-500': !dropdown }" class="inline-flex items-center h-full space-x-2 text-base font-medium leading-6 text-gray-500 transition duration-150 ease-in-out cursor-pointer select-none group hover:text-wave-600 focus:outline-none focus:text-wave-600">
            <span>{{ __('menu.Shipping') }}</span>
            <svg class="w-5 h-5 text-gray-400 transition duration-150 ease-in-out group-hover:text-wave-600 group-focus:text-wave-600" x-bind:class="{ 'text-wave-600': dropdown, 'text-gray-400': !dropdown }" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </div>

        <div
            x-show="dropdown"
            x-transition:enter="duration-200 ease-out scale-95"
            x-transition:enter-start="opacity-50 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition duration-100 ease-in scale-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute mega-menu-card  w-screen max-w-lg px-2 transform -translate-x-1/2 left-1/2 sm:px-0"
            x-cloak>

            <div class="overflow-hidden shadow-lg xl:rounded-xl">
                <div class="drop-down-card overflow-hidden bg-white shadow-xs xl:rounded-xl">

                    <div class="nav-dropdown">
                        
                            <div class="mega-menu-head">
                                <h3>{{ __('menu.Shipping') }}</h3>
                            </div>
                            <div class="mega-menu-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <ul class="sub-menu-links" role="presentation">
                                          <li><a href="/get-quote">{{ __('menu.Get a Quote') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>  
                                          <li><a href="javascript:void(0);">{{ __('menu.Create a Shipment') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                       </ul>
                                    </div>
                                   <div class="col-lg-6">
                                        <ul class="sub-menu-links" role="presentation">
                                        <li><a href="{{route('wave.getQuotationForm')}}">{{ __('menu.Schedule a Pickup') }}<span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                        @if(!(auth()->guest()))
                                        <li><a href="">{{ __('menu.Shipping History') }}<span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                        @endif
                                       </ul>
                                    </div>
                                </div>
                            </div>
                    </div>

                </div>
            </div>
        </div>
    </div>




<div @mouseenter="dropdown = true" @mouseleave="dropdown=false" @click.away="dropdown=false" x-data="{ dropdown: false }" class="relative h-full select-none">
        <div @click="dropdown = !dropdown" :class="{ 'text-wave-600': dropdown, 'text-gray-500': !dropdown }" class="inline-flex items-center h-full space-x-2 text-base font-medium leading-6 text-gray-500 transition duration-150 ease-in-out cursor-pointer select-none hover:text-wave-600 focus:outline-none focus:text-wave-500">
            <span>{{ __('menu.Tracking') }}</span>
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </div>

        <div
            x-show="dropdown"
            x-transition:enter="duration-200 ease-out scale-95"
            x-transition:enter-start="opacity-50 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition duration-100 ease-in scale-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute mega-menu-card  w-screen max-w-lg px-2 transform -translate-x-1/2 left-1/2 sm:px-0"
            x-cloak>
            <div class="shadow-lg rounded-xl">
                <div class="overflow-hidden border border-gray-100 shadow-md rounded-xl">
                    <div class="relative z-20 grid bg-white  drop-down-card nav-dropdown">
                       <div class="mega-menu-head">
                                <h3>{{ __('menu.Tracking') }}</h3>
                            </div>
                            <div class="mega-menu-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <ul class="sub-menu-links" role="presentation">
                                          <li><a href="javascript:void(0);">{{ __('menu.Track a Package') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                       </ul>
                                    </div>
                                    <div class="col-lg-6">
                                        <ul class="sub-menu-links" role="presentation">
                                          <li><a href="javascript:void(0);">{{ __('menu.Change a Delivery') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                       </ul>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div @mouseenter="dropdown = true" @mouseleave="dropdown=false" @click.away="dropdown=false" x-data="{ dropdown: false }" class="relative h-full select-none">
        <div :class="{ 'text-wave-600': dropdown, 'text-gray-500': !dropdown }" class="inline-flex items-center h-full space-x-2 text-base font-medium leading-6 text-gray-500 transition duration-150 ease-in-out cursor-pointer select-none group hover:text-wave-600 focus:outline-none focus:text-wave-600">
            <span>{{ __('menu.Service Navigator') }}</span>
            <svg class="w-5 h-5 text-gray-400 transition duration-150 ease-in-out group-hover:text-wave-600 group-focus:text-wave-600" x-bind:class="{ 'text-wave-600': dropdown, 'text-gray-400': !dropdown }" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </div>

       <div
            x-show="dropdown"
            x-transition:enter="duration-200 ease-out scale-95"
            x-transition:enter-start="opacity-50 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition duration-100 ease-in scale-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="mega-menu-card absolute w-screen max-w-lg px-2 transform -translate-x-1/2 left-1/2 sm:px-0"
            x-cloak>

            <div class="overflow-hidden shadow-lg xl:rounded-xl">
                <div class="drop-down-card overflow-hidden bg-white shadow-xs xl:rounded-xl">

                    <div class="">
                        
                           <div class="mega-menu-head">
                                <h3>{{ __('menu.Service Navigator') }}</h3>
                            </div>
                            <div class="mega-menu-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <ul class="sub-menu-links" role="presentation">
                                          <li><a href="javascript:void(0);">{{ __('menu.Manage Shipment') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                          <li><a href="javascript:void(0);">{{ __('menu.Upgrade Account') }}<span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                          <li><a href="javascript:void(0);">{{ __('menu.Our locations') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                          <li><a href="{{route('wave.officeSupplies')}}">{{ __('menu.Order Supplies') }}<span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                       </ul>
                                    </div>
                                    <div class="col-lg-6">
                                        <ul class="sub-menu-links" role="presentation">
                                          <li><a href="javascript:void(0);">{{ __('menu.Pay a Shipment') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                          <li><a href="javascript:void(0);">{{ __('menu.Verify a payment') }}<span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                          <li><a href="javascript:void(0);">{{ __('menu.Discount a shipment') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                          <li><a href="javascript:void(0);">{{ __('menu.Billing section') }}<span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>                    
                                       </ul>
                                    </div>
                                </div>
                            </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div @mouseenter="dropdown = true" @mouseleave="dropdown=false" @click.away="dropdown=false" x-data="{ dropdown: false }" class="relative h-full select-none">
        <div :class="{ 'text-wave-600': dropdown, 'text-gray-500': !dropdown }" class="inline-flex items-center h-full space-x-2 text-base font-medium leading-6 text-gray-500 transition duration-150 ease-in-out cursor-pointer select-none group hover:text-wave-600 focus:outline-none focus:text-wave-600">
            <span>{{ __('menu.Help and Support') }}</span>
            <svg class="w-5 h-5 text-gray-400 transition duration-150 ease-in-out group-hover:text-wave-600 group-focus:text-wave-600" x-bind:class="{ 'text-wave-600': dropdown, 'text-gray-400': !dropdown }" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </div>

        <div
            x-show="dropdown"
            x-transition:enter="duration-200 ease-out scale-95"
            x-transition:enter-start="opacity-50 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition duration-100 ease-in scale-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute mega-menu-card  w-screen max-w-lg px-2 transform -translate-x-1/2 left-1/2 sm:px-0"
            x-cloak>

            <div class="overflow-hidden shadow-lg xl:rounded-xl">
                <div class="drop-down-card overflow-hidden bg-white shadow-xs xl:rounded-xl">

                    <div class="">
                        
                           <div class="mega-menu-head">
                                <h3>{{ __('menu.Help and Support') }}</h3>
                            </div>
                            <div class="mega-menu-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <ul class="sub-menu-links" role="presentation">
                                          <li><a href="javascript:void(0);">{{ __('menu.Chat wih us') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li> 
                                          <li><a href="{{route('wave.ContactUs')}}">{{ __('menu.Contact us') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                       </ul>
                                    </div>
                                    <div class="col-lg-6">
                                        <ul class="sub-menu-links" role="presentation">
                                          <li><a href="{{route('tickets.create')}}">{{ __('menu.Submit a Ticket') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                          <li><a href="{{route('wave.FileClaim')}}">{{ __('menu.File a claim') }} <span class="icon ups-icon-right-arrow" aria-hidden="true"></span></a></li>
                                       </ul>
                                    </div>
                                </div>
                            </div>
                    </div>

                </div>
            </div>
        </div>
    </div>





   <!--  <a href="/#pricing" class="text-base font-medium leading-6 text-gray-500 transition duration-150 ease-in-out hover:text-wave-600 focus:outline-none focus:text-wave-600">
        Pricing
    </a>
    <a href="{{ route('wave.blog') }}" class="text-base font-medium leading-6 text-gray-500 transition duration-150 ease-in-out hover:text-wave-600 focus:outline-none focus:text-wave-600">
        Blog
    </a> -->

    
    @if(auth()->guest())
    <div class="w-1 h-5 mx-10 border-r border-gray-300"></div>
    <a href="{{ route('login') }}" class="text-base font-medium leading-6 text-gray-500 whitespace-no-wrap hover:text-wave-600 focus:outline-none focus:text-gray-900">
    {{ __('menu.Sign in') }}
    </a>
    <span class="inline-flex rounded-md shadow-sm">
        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2 text-base font-medium leading-6 text-white whitespace-no-wrap transition duration-150 ease-in-out border border-transparent rounded-md bg-wave-500 hover:bg-wave-600 focus:outline-none focus:border-indigo-700 focus:shadow-outline-wave active:bg-wave-700">
        {{ __('menu.Sign up') }}
        </a>
    </span>
    @else
    <div class="w-1 h-5 mx-10 border-r border-gray-300"></div>
    <a href="/settings/profile" class="text-base font-medium leading-6 text-gray-500 whitespace-no-wrap hover:text-wave-600 focus:outline-none focus:text-gray-900">
    {{ __('menu.My Account') }}
    </a>
    <span class="inline-flex rounded-md shadow-sm sign-button">
        <a href="{{ route('wave.logout') }}" class="inline-flex items-center justify-center px-4 py-2 text-base font-medium leading-6 text-white whitespace-no-wrap transition duration-150 ease-in-out border border-transparent rounded-md bg-wave-500 hover:bg-wave-600 focus:outline-none focus:border-indigo-700 focus:shadow-outline-wave active:bg-wave-700">
        {{ __('menu.Sign Out') }}
        </a> 
    </span>   
    @endif
</nav>
