@extends('theme::layouts.app')

@section('content')

<section class="main-banner-sec">
<div class="overlay"></div>
<video playsinline="playsinline" autoplay="autoplay" muted="muted" loop="loop">
    <source src="{{asset('themes/'.$theme->folder.'/video/Video.mp4')}}" type="video/mp4">
  </video>

  <div class="container max-w-7xl">
    <div class="row">
      <div class="col-lg-9 offset-lg-1">
        <ul class="quick-list">
          <li>
            <a href="javascript:void(0);" class="quick-link active vc-track">Track <span class="drop-arrow"><i class="fas fa-chevron-down"></i></span></a>
            <div class="quick-list-card mobile_transparent open">
              <div class="form-group">
                <label class="form-label white-text">Tracking Number <a href="javascript:void(0);" class="help-info ml-2"><i class="fas fa-info-circle"></i></a></label>
                <div class="track-input">
                  <input type="text" name="" class="form-control" id="tracking_number">
                  <a href="javascript:void(0)" class="track-btn btn tracking_link">Track</a>
                </div>
                <div class="alert alert-danger result_not_found"></div>
                <span class="help-text white-text">Need help changing your delivery? <a href="javascript:void(0);" class="normal-link">Get Help</a></span>
              </div>
            </div>
          </li>
          <li>
            <a href="javascript:void(0);" class="quick-link vc-quote">Quote <span class="drop-arrow"><i class="fas fa-chevron-down"></i></span></a>
            <div class="quick-list-card">
              <span class="help-text white-text d-flex mb-4">Enter the origin and the destination address to get a quote. <a href="/get-quote" class="normal-link" style="padding-left: 5px;"> get a quote here.</a></span>
              <form id="get_quote_form">
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group">
                    <input type="hidden" name="from_country_name" id="from_country_name" class="form-control disable_delivery_info" value="UNITED STATES">
                    <label class="form-label white-text">From<span class="required">*</span></label>                                     
                    <input type="text" name="from_address_show" id="from_address_show" class="form-control disable_delivery_info pac-target-input">
                  </div>
                </div>
                <input type="hidden" name="from_address" id="from_address" class="form-control disable_delivery_info pac-target-input">
                <input type="hidden" class="form-control disable_delivery_info" required name="from_zip"  id="from_zip" value="{{(isset($quote_request['from_zip']) && !empty($quote_request['from_zip']))?$quote_request['from_zip']:''}}">
                <input type="hidden" class="form-control disable_delivery_info" required name="from_city" id="from_city" value="{{(isset($quote_request['from_city']) && !empty($quote_request['from_city']))?$quote_request['from_city']:''}}">
                <input type="hidden" class="form-control disable_delivery_info" required name="from_state" id="from_state" value="{{(isset($quote_request['from_state']) && !empty($quote_request['from_state']))?$quote_request['from_state']:''}}">
                <div class="col-lg-6">
                  <div class="form-group">
                    <input type="hidden" name="to_country" id="to_country" class="form-control disable_delivery_info" value="">
                    <label class="form-label white-text">To<span class="required">*</span></label>                                     
                    <input type="text" name="to_address_show" id="to_address_show" class="form-control disable_delivery_info pac-target-input">                                      
                  </div>
                </div>
              </div>
              <input type="hidden" name="to_address" id="to_address" class="form-control disable_delivery_info pac-target-input">                                      
              <input type="hidden" class="form-control disable_delivery_info required" name="to_zip" id="to_zip" value="{{(isset($quote_request['to_zip']) && !empty($quote_request['to_zip']))?$quote_request['to_zip']:''}}">
              <input type="hidden" class="form-control disable_delivery_info required" name="to_city" id="to_city" value="{{(isset($quote_request['to_city']) && !empty($quote_request['to_city']))?$quote_request['to_city']:''}}">
              <input type="hidden" class="form-control disable_delivery_info required" name="to_state" id="to_state"  value="{{(isset($quote_request['to_state']) && !empty($quote_request['to_state']))?$quote_request['to_state']:''}}">
              <h4 class="card-heading white-text">Package Information</h4>
              <div class="row">
                <div class="col-lg-3 col-xs-12">
                  <div class="form-group">
                    <label class="form-label white-text">Weight (lbs)<span class="required">*</span></label>
                    <input type="text" class="form-control disable_delivery_info" required name="dimensions[weight][]" placeholder="Weight in lbs">                                     
                  </div>
                </div>
                <div class="col-lg-3 col-xs-4">
                  <div class="form-group">
                    <label class="form-label white-text">Length (in)</label>                                     
                    <input type="text" class="form-control disable_delivery_info" required name="dimensions[length][]" placeholder="Length in inches">                                      
                  </div>
                </div>
                <div class="col-lg-3 col-xs-4">
                  <div class="form-group">
                    <label class="form-label white-text">Width (in)</label>                                     
                    <input type="text" class="form-control disable_delivery_info" required name="dimensions[width][]" placeholder="Width in inches">                                      
                  </div>
                </div>
                <div class="col-lg-3 col-xs-4">
                  <div class="form-group">
                    <label class="form-label white-text">Height (in)</label>                                     
                    <input type="text" class="form-control disable_delivery_info" required name="dimensions[height][]" placeholder="Height in inches">                                      
                  </div>
                </div>
                <div class="col-lg-12 d-flex justify-content-center">
                  <button type="submit" class="cstm-btn">Get Quotes</button>
                </div>
                </form>
                <div class="pickup-form-zion">
                  <div class="col-md-4 mt-5">
                    <div class="date-heading">
                      <h2>DHL</h2>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="zion-radio-box bg-white border border-gray-200 rounded-lg shadow-xl">
                          <input class="zion-radio-btn" id="radio1" type="radio" name="radio" value="radio1" checked>
                          <label class="zion-radio-btn-label" for="radio1">
                            <span class="font-weight-bold">$333.15</span>
                            <h2 class="font-weight-bold">DELIVERED BY</h2>
                            <h3>Express worldwide doc <br>At 11:59 PM </h3>
                          </label>
                        </div>
                        <div class="zion-radio-box bg-white border border-gray-200 rounded-lg shadow-xl">
                          <input class="zion-radio-btn-second" id="radio2" type="radio" name="radio" value="radio2">
                          <label class="zion-radio-btn-label" for="radio2">
                            <span class="font-weight-bold">$333.15</span>
                            <h2 class="font-weight-bold">DELIVERED BY</h2>
                            <h3>Express worldwide doc <br>June 10, 2020 </h3>
                          </label>
                        </div>
                        <div class="zion-radio-box bg-white border border-gray-200 rounded-lg shadow-xl">
                          <input class="zion-radio-btn-third" id="radio2" type="radio" name="radio" value="radio2">
                          <label class="zion-radio-btn-label" for="radio2">
                            <span class="font-weight-bold">$333.15</span>
                            <h2 class="font-weight-bold">DELIVERED BY</h2>
                            <h3>Express worldwide doc <br>June 10, 2020 </h3>
                          </label>
                        </div>
                        <div class="zion-radio-box bg-white border border-gray-200 rounded-lg shadow-xl">
                          <input class="zion-radio-btn-fourth" id="radio2" type="radio" name="radio" value="radio2">
                          <label class="zion-radio-btn-label" for="radio2">
                            <span class="font-weight-bold">$333.15</span>
                            <h2 class="font-weight-bold">DELIVERED BY</h2>
                            <h3>Express worldwide doc <br>June 10, 2020 </h3>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mt-5">
                    <div class="date-heading">
                      <h2>FedEx</h2>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="zion-radio-box bg-white border border-gray-200 rounded-lg shadow-xl">
                          <input class="zion-radio-btn-fifth" id="radio5" type="radio" name="radio" value="radio5" checked>
                          <label class="zion-radio-btn-label" for="radio5">
                            <span class="font-weight-bold">$333.15</span>
                            <h2 class="font-weight-bold">DELIVERED BY</h2>
                            <h3>Express worldwide doc <br> June 10, 2020 </h3>
                          </label>
                        </div>
                        <div class="zion-radio-box bg-white border border-gray-200 rounded-lg shadow-xl">
                          <input class="zion-radio-btn-six" id="radio6" type="radio" name="radio" value="radio6">
                          <label class="zion-radio-btn-label" for="radio6">
                            <span class="font-weight-bold">$333.15</span>
                            <h2 class="font-weight-bold">DELIVERED BY</h2>
                            <h3>Express worldwide doc <br>June 10, 2020 </h3>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mt-5">
                    <div class="date-heading pseudo-plus">
                      <h2>Canada Post</h2>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="zion-radio-box bg-white border border-gray-200 rounded-lg shadow-xl">
                          <input class="zion-radio-btn-seven" id="radio7" type="radio" name="radio" value="radio7" checked>
                          <label class="zion-radio-btn-label" for="radio7">
                            <span class="font-weight-bold">$333.15</span>
                            <h2 class="font-weight-bold">DELIVERED BY</h2>
                            <h3>Express worldwide doc <br>June 10, 2020 </h3>
                          </label>
                        </div>
                        <div class="zion-radio-box bg-white border border-gray-200 rounded-lg shadow-xl">
                          <input class="zion-radio-btn-eight" id="radio8" type="radio" name="radio" value="radio8">
                          <label class="zion-radio-btn-label" for="radio8">
                            <span class="font-weight-bold">$333.15</span>
                            <h2 class="font-weight-bold">DELIVERED BY</h2>
                            <h3>Express worldwide doc <br>June 10, 2020 </h3>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <a href="#" class="zion-ship-btn"><i class="fas fa-chevron-left"></i> Back</a>
                    <a href="#" class="zion-ship-btn-second">Start shipping <i class="fas fa-chevron-right"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </li>
          <li><a href="javascript:void(0);" class="quick-link vc-track">Ship</a></li>
          <li><a href="{{url('schedule-pickup')}}" class="quick-link">Pickup</a></li>
        </ul>
      </div>
    </div>
	
	
  <!-- home page gallery -->
  
	<div class="row">
		<div class="col-md-12">
			<div class="inline_gallery">
				<figure class="gallery-item">
					<div class="gallery-icon portrait">
						<img src="{{asset('themes/'.$theme->folder.'/images/banner-images/slider_img_1.webp')}}" class="attachment-full size-full" alt="" width="51" height="55">
					</div>
				</figure>
				<figure class="gallery-item">
					<div class="gallery-icon landscape"> 
						<img src="{{asset('themes/'.$theme->folder.'/images/banner-images/slider_img_2.webp')}}" class="attachment-full size-full" alt="" width="112" height="4">
					</div>
				</figure>
				<figure class="gallery-item">
					<div class="gallery-icon landscape">
						<img src="{{asset('themes/'.$theme->folder.'/images/banner-images/slider_img_3.webp')}}" class="attachment-full size-full" alt="" width="60" height="48">
					</div>
				</figure>
				<figure class="gallery-item">
					<div class="gallery-icon landscape">
						<img src="{{asset('themes/'.$theme->folder.'/images/banner-images/slider_img_4.webp')}}" class="attachment-full size-full" alt="" width="112" height="4">
					</div>
				</figure>
				<figure class="gallery-item">
					<div class="gallery-icon portrait">
						<img src="{{asset('themes/'.$theme->folder.'/images/banner-images/slider_img_5.webp')}}" class="attachment-full size-full" alt="" width="58" height="60">
					</div>
				</figure>
				<figure class="gallery-item">
					<div class="gallery-icon landscape">
						<img src="{{asset('themes/'.$theme->folder.'/images/banner-images/slider_img_6.webp')}}" class="attachment-full size-full" alt="" width="112" height="4">
					</div>
				</figure>
				<figure class="gallery-item">
					<div class="gallery-icon landscape">
						<img src="{{asset('themes/'.$theme->folder.'/images/banner-images/slider_img_7.webp')}}" class="attachment-full size-full" alt="" width="68" height="54">
					</div>
				</figure>
				<figure class="gallery-item">
					<div class="gallery-icon landscape">
						<img src="{{asset('themes/'.$theme->folder.'/images/banner-images/slider_img_8.webp')}}" class="attachment-full size-full" alt="" width="112" height="4">
					</div>
				</figure>
				<figure class="gallery-item">
					<div class="gallery-icon portrait">
						<img src="{{asset('themes/'.$theme->folder.'/images/banner-images/slider_img_9.webp')}}" class="attachment-full size-full" alt="" width="48" height="55">
					</div>
				</figure>
			</div>
		</div>
	</div>	
  
  <!-- home page gallery -->
	
  </div>
</section>
<section class="about-us">
  <div class="container max-w-7xl">
    <div class="sec-heading text-center">
      <h2 class="about-heading">WHAT MAKES US <span>DIFFERENT?</span></h2>
      <p class="content-para">Our four main characteristics</p>
    </div>
    <div class="row">
      <div class="col-lg-3 col-sm-6 vc_column">
        <div class="wpb_wrapper">
			<div class="icon-box  ">
				<div class="inner">
					<figure> <img src="{{asset('themes/'.$theme->folder.'/images/banner-images/delivered.png')}}" alt="icon01"> </figure>
					<h4>Delivery Time</h4>
					<p>We are required to respect and guarantee the date of our deliveries.</p> 
				</div>
			</div>
		</div>
      </div>
      <div class="col-lg-3 col-sm-6 vc_column">
        <div class="wpb_wrapper">
			<div class="icon-box  ">
				<div class="inner">
					<figure> <img src="{{asset('themes/'.$theme->folder.'/images/banner-images/secure.png')}}" alt="icon01"> </figure>
					<h4>Your Packages are secure </h4>
					<p>Our great concern is to reassure ourselves that your belongings are safe.</p>
				</div>
			</div>
		</div> 
      </div>
      <div class="col-lg-3 col-sm-6 vc_column">
        <div class="wpb_wrapper">
			<div class="icon-box  ">
				<div class="inner">
					<figure> <img src="{{asset('themes/'.$theme->folder.'/images/banner-images/door-to-door.png')}}" alt="icon01"> </figure>
					<h4>Door-to-door Service</h4>
					<p>Our home pickup and delivery service saves our customers time.</p>
				</div>
			</div>
		</div> 
      </div>
      <div class="col-lg-3 col-sm-6 vc_column">
        <div class="wpb_wrapper">
			<div class="icon-box  ">
				<div class="inner">
					<figure> <img src="{{asset('themes/'.$theme->folder.'/images/banner-images/localization.png')}}" alt="icon01"> </figure>
					<h4>Service Points</h4>
					<p>Our authorized agents are almost everywhere even in the most remote areas.</p>
				</div>
			</div>
		</div> 
      </div>
    </div>
  </div>
</section>
<!-- <div class="relative flex items-center w-full">
  <div class="relative z-20 px-8 mx-auto xl:px-5 max-w-7xl">
  
  <div class="flex flex-col items-center h-full pt-16 pb-56 lg:flex-row">
  
  <div class="flex flex-col items-start w-full mb-16 md:items-center lg:pr-12 lg:items-start lg:w-1/2 lg:mb-0">
  
  <h2 class="invisible text-sm font-semibold tracking-wide text-gray-700 uppercase transition-none duration-700 ease-out transform translate-y-12 opacity-0 sm:text-base lg:text-sm xl:text-base" data-replace='{ "transition-none": "transition-all", "invisible": "visible", "translate-y-12": "translate-y-0", "scale-110": "scale-100", "opacity-0": "opacity-100" }'>{{ theme('home_headline') }}</h2>
  <h1 class="invisible pb-2 mt-3 text-4xl font-extrabold leading-10 tracking-tight text-transparent transition-none duration-700 ease-out delay-150 transform translate-y-12 opacity-0 bg-clip-text bg-gradient-to-r from-blue-600 via-blue-500 to-purple-600 scale-10 md:my-5 sm:leading-none lg:text-5xl xl:text-6xl" data-replace='{ "transition-none": "transition-all", "invisible": "visible", "translate-y-12": "translate-y-0", "scale-110": "scale-100", "opacity-0": "opacity-100" }'>{{ theme('home_subheadline') }}</h1>
  <p class="invisible max-w-2xl mt-0 text-base text-left text-gray-600 transition-none duration-700 ease-out delay-300 transform translate-y-12 opacity-0 md:text-center lg:text-left sm:mt-2 md:mt-0 sm:text-base lg:text-lg xl:text-xl" data-replace='{ "transition-none": "transition-all", "invisible": "visible", "translate-y-12": "translate-y-0", "scale-110": "scale-100", "opacity-0": "opacity-100" }'>{{ theme('home_description') }}</p>
  <div class="invisible w-full mt-5 transition-none duration-700 ease-out transform translate-y-12 opacity-0 delay-450 sm:mt-8 sm:flex sm:justify-center lg:justify-start sm:w-auto" data-replace='{ "transition-none": "transition-all", "invisible": "visible", "translate-y-12": "translate-y-0", "opacity-0": "opacity-100" }'>
  <div class="rounded-md">
  <a href="{{ theme('home_cta_url') }}" class="flex items-center justify-center w-full px-8 py-3 text-base font-medium leading-6 text-white transition duration-150 ease-in-out border border-transparent rounded-md bg-wave-500 hover:bg-wave-600 focus:outline-none focus:border-wave-600 focus:shadow-outline-indigo md:py-4 md:text-lg md:px-10">
  {{ theme('home_cta') }}
  </a>
  </div>
  <div class="mt-3 sm:mt-0 sm:ml-3">
  <a href="#" class="flex items-center justify-center w-full px-8 py-3 text-base font-medium leading-6 text-indigo-700 transition duration-150 ease-in-out bg-indigo-100 border-2 border-transparent rounded-md hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 md:py-4 md:text-lg md:px-10">
  Learn More
  </a>
  </div>
  </div>
  </div>
  
  <div class="flex w-full mb-16 lg:w-1/2 lg:mb-0">
  
  <div class="relative invisible transition-none duration-1000 delay-100 transform translate-x-12 opacity-0" data-replace='{ "transition-none": "transition-all", "invisible": "visible", "translate-x-12": "translate-y-0", "opacity-0": "opacity-100" }'>
  <img src="{{ Voyager::image(theme('home_promo_image')) }}" class="w-full max-w-3xl sm:w-auto">
  </div>
  
  </div>
  </div>
  </div>
  
  
  
  </div> -->
<!--  <div class="relative z-40 -mt-64">
  <svg viewBox="0 0 120 28" class="-mt-64">
  <defs>
  <path id="wave" d="M 0,10 C 30,10 30,15 60,15 90,15 90,10 120,10 150,10 150,15 180,15 210,15 210,10 240,10 v 28 h -240 z" />
  </defs>
  <use id="wave3" class="wave" xlink:href="#wave" x="0" y="-2"></use>
  <use id="wave2" class="wave" xlink:href="#wave" x="0" y="0"></use>
  <use id="wave1" class="wave" xlink:href="#wave" x="0" y="1" />
  </svg>
  </div>
  
  {{-- FEATURES SECTION --}}
  <section class="relative z-40 w-full pt-10 pb-16 lg:pt-5 xl:-mt-24 bg-gradient-to-b from-wave-500 via-wave-600 to-wave-400">
  
  <div class="absolute top-0 left-0 z-10 w-full h-full transform -translate-x-1/2 opacity-10">
  <svg class="w-full h-full text-white opacity-25 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 205 205"><defs/><g fill="#FFF" fill-rule="evenodd"><path d="M182.63 37c14.521 18.317 22.413 41.087 22.37 64.545C205 158.68 159.1 205 102.486 205c-39.382-.01-75.277-22.79-92.35-58.605C-6.939 110.58-2.172 68.061 22.398 37a105.958 105.958 0 00-9.15 43.352c0 54.239 39.966 98.206 89.265 98.206 49.3 0 89.265-43.973 89.265-98.206A105.958 105.958 0 00182.629 37z"/><path d="M103.11 0A84.144 84.144 0 01150 14.21C117.312-.651 78.806 8.94 56.7 37.45c-22.105 28.51-22.105 68.58 0 97.09 22.106 28.51 60.612 38.101 93.3 23.239-30.384 20.26-70.158 18.753-98.954-3.75-28.797-22.504-40.24-61.021-28.47-95.829C34.346 23.392 66.723.002 103.127.006L103.11 0z"/><path d="M116.479 13c36.655-.004 67.014 28.98 69.375 66.234 2.36 37.253-24.089 69.971-60.44 74.766 29.817-8.654 48.753-38.434 44.308-69.685-4.445-31.25-30.9-54.333-61.904-54.014-31.003.32-56.995 23.944-60.818 55.28v-1.777C46.99 44.714 78.096 13.016 116.479 13z"/></g></svg>
  </div>
  
  <div class="relative z-20 flex flex-col items-start justify-start px-8 mx-auto sm:items-center max-w-7xl xl:px-5">
  <h2 class="text-4xl font-medium leading-9 text-white">Awesome Features</h2>
  <p class="mt-4 leading-6 sm:text-center text-wave-200">Wave has some cool features to help you rapidly build your Software as a Service.<br class="hidden md:block"> Here are a few awesome features you're going to love!</p>
  
  <div class="grid mt-16 gap-y-10 sm:grid-cols-2 sm:gap-x-8 md:gap-x-12 lg:grid-cols-3 xl:grid-cols-4 lg:gap-20">
  @foreach(config('features') as $feature)
  <div>
  <img src="{{ $feature->image }}" class="w-16 rounded sm:mx-auto">
  <h3 class="mt-6 text-sm font-semibold leading-6 sm:text-center text-wave-100">{{ $feature->title }}</h3>
  <p class="mt-2 text-sm leading-5 sm:text-center text-wave-200">{{ $feature->description }}</p>
  </div>
  @endforeach
  </div>
  
  </div>
  </section>
  
  <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" class="bg-gray-100" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
  viewBox="0 0 1440 156" style="enable-background:new 0 0 1440 126;" xml:space="preserve">
  <style type="text/css">
  .wave-svg{fill:#0069ff;}
  .wave-svg-lighter{fill:#4c95fe}
  </style>
  <g fill-rule="nonzero">
  <path class="wave-svg" d="M694,94.437587 C327,161.381336 194,153.298248 0,143.434189 L2.01616501e-13,44.1765618 L1440,27 L1440,121 C1244,94.437587 999.43006,38.7246898 694,94.437587 Z" id="Shape" fill="#0069FF" opacity="0.519587054"></path>
  <path class="wave-svg" d="M686.868924,95.4364002 C416,151.323752 170.73341,134.021565 1.35713663e-12,119.957876 L0,25.1467017 L1440,8 L1440,107.854321 C1252.11022,92.2972893 1034.37894,23.7359827 686.868924,95.4364002 Z" id="Shape" fill="#0069FF" opacity="0.347991071"></path>
  <path class="wave-svg-lighter" d="M685.6,30.8323303 C418.7,-19.0491687 170.2,1.94304528 0,22.035593 L0,118 L1440,118 L1440,22.035593 C1252.7,44.2273621 1010,91.4098622 685.6,30.8323303 Z" transform="translate(720.000000, 59.000000) scale(1, -1) translate(-720.000000, -59.000000) "></path>
  </g>
  </svg> -->
  <!-- NEWS AND BLOG SECTIONS STARTS HERE -->
	<div class="news-section">
		<div id="particles-js"></div>
		<div class="container">
			<div class="sec-heading text-center">
			  <h2 class="about-heading" style="color:#ffffff">Thinking Ahead - Moving Forward</h2>
			  <p class="content-para" style="color:#ffffff">What’s News Today. Recent Blog Entries.</p>
			</div>
			<div class="row">
				<div class="col-md-4">
					<div class="ozy-simlple-info-box " id="simple-info-box-first" style="height: 247px;">
						<section>
							<h5>// 01</h5>
							<h3>Great savings for companies</h3>
							<p>Save 35% on selected services. Terms and Conditions apply</p>
							<a href="#" target="_self">LEARN MORE</a>
						</section>
					</div>
				</div>
				<div class="col-md-4">
					<div class="ozy-simlple-info-box " id="simple-info-box-second" style="height: 247px;">
						<section>
							<h5>// 02</h5>
							<h3>Great savings for companies</h3>
							<p>Save 35% on selected services. Terms and Conditions apply</p>
							<a href="#" target="_self">LEARN MORE</a>
						</section>
					</div>
				</div>
				<div class="col-md-4">
					<div class="ozy-simlple-info-box " id="simple-info-box-third" style="height: 247px;">
						<section>
							<h5>// 03</h5>
							<h3>Great savings for companies</h3>
							<p>Save 35% on selected services. Terms and Conditions apply</p>
							<a href="#" target="_self">LEARN MORE</a>
						</section>
					</div>
				</div>
			</div>
		</div>
	</div>
  
  
  
  <!-- NEWS AND BLOG SECTIONS ENDS HERE -->
  
<!-- BEGINNING OF TESTIMONIALS SECTION -->
<div id="testimonials" class="annoucement-sec d-none">
  <div class="relative z-40 w-full pt-10 pb-16 lg:pt-5 xl:-mt-24 bg-gradient-to-b from-wave-500 via-wave-600 to-wave-400">
    <div class="max-w-6xl px-10 pb-20 mx-auto annoucement-block">
      <div class="flex flex-col items-center lg:flex-row">
        <div class="flex flex-col justify-center w-full h-full mb-10 lg:pr-8 sm:w-4/5 md:items-center lg:mb-0 lg:items-start md:w-3/5 lg:w-1/2">
          <p class="mb-2 text-base font-medium tracking-tight uppercase text-wave-500">Lorem ipsum is simply dummy text</p>
          <h2
            class="text-4xl font-extrabold leading-10 tracking-tight text-white sm:leading-none lg:text-5xl xl:text-6xl">
            Announcements
          </h2>
          <p class="pr-5 my-6 text-lg text-white md:text-center lg:text-left">This is an example section of where you will add your announcements for your Software as a Service.</p>
          <a href="#_"
            class="flex items-center justify-center px-8 py-3 text-base font-medium leading-6 text-white transition duration-150 ease-in-out border border-transparent rounded-md shadow bg-wave-600 hover:bg-wave-500 focus:outline-none focus:border-wave-700 focus:shadow-outline-wave md:py-4 md:text-lg md:px-10">View All Announcements</a>
        </div>
        <div class="w-full sm:w-4/5 lg:w-1/2">
          @foreach ($announcements as $announcement)
          <blockquote class="flex flex-row-reverse items-center justify-between w-full col-span-1 p-6 my-5 bg-white rounded-lg shadow sm:flex-row">
            <div class="flex flex-col pl-5 sm:pr-8">
              <div class="relative sm:pl-12">
                <span class="absolute left-0 hidden w-10 h-10 fill-current sm:block text-wave-500"><i class="fas fa-bullhorn"></i></span>
                <h4 class="annoucement-heading mb-3">{{$announcement->title}}</h4>
                <p class="mt-2 text-base text-gray-600">{{$announcement->description}}</p>
              </div>
              <h3 class="mt-3 text-base font-medium leading-5 text-gray-800 truncate sm:pl-12">Annoucement by:<span
                class="mt-1 text-sm leading-5 text-gray-500 truncate">- CEO Zion Shipping</span></h3>
              <p class="mt-1 text-sm leading-5 text-gray-500 truncate"></p>
            </div>
          </blockquote>
          @endforeach
          <!-- <blockquote
            class="flex flex-row-reverse items-center justify-between w-full col-span-1 p-6 my-5 bg-white rounded-lg shadow sm:flex-row">
            <div class="flex flex-col pl-5 sm:pr-8">
            <div class="relative sm:pl-12">
            <span class="absolute left-0 hidden w-10 h-10 fill-current sm:block text-wave-500"><i class="fas fa-bullhorn"></i></span>
            <h4 class="annoucement-heading mb-3">Lorem ipsum is simply dummy.</h4>
            <p class="mt-2 text-base text-gray-600">Wave allowed me to build the Software as a Service of my dreams!
            </p>
            </div>
            
            <h3 class="mt-3 text-base font-medium leading-5 text-gray-800 truncate sm:pl-12">Annoucement by:<span
            class="mt-1 text-sm leading-5 text-gray-500 truncate">- CEO SomeCompany</span></h3>
            <p class="mt-1 text-sm leading-5 text-gray-500 truncate"></p>
            </div>
            
            </blockquote>
            <blockquote
            class="flex flex-row-reverse items-center justify-between w-full col-span-1 p-6 bg-white rounded-lg shadow sm:flex-row">
            <div class="flex flex-col pl-5 sm:pr-8">
            <div class="relative sm:pl-12">
            <span class="absolute left-0 hidden w-10 h-10 fill-current sm:block text-wave-500"><i class="fas fa-bullhorn"></i></span>
            <h4 class="annoucement-heading mb-3">Lorem ipsum is simply dummy.</h4>
            <p class="mt-2 text-base text-gray-600">Wave allowed me to build the Software as a Service of my dreams!
            </p>
            </div>
            
            <h3 class="mt-3 text-base font-medium leading-5 text-gray-800 truncate sm:pl-12">Annoucement by:<span
            class="mt-1 text-sm leading-5 text-gray-500 truncate">- CEO SomeCompany</span></h3>
            <p class="mt-1 text-sm leading-5 text-gray-500 truncate"></p>
            </div>
            
            </blockquote> -->
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END OF TESTIMONIALS SECTION -->
<!-- BEGINNING OF PRICING SECTION -->
<div id="pricing" class="relative">
  <div class="relative z-20 px-8 pb-8 mx-auto max-w-7xl xl:px-5">
    <div class="w-full text-left sm:text-center">
      <h2 class="about-heading">SUBSCRIPTION  <span>PLANS</span></h2>
      <p class="content-para">It's easy to subscribe to any of our four plans either to Yearly or Monthly basis.</p>
    </div>
    @include('theme::partials.plans')
    <p class="w-full my-8 text-left text-gray-500 sm:my-10 sm:text-center d-none">All plans are fully configurable in the Admin Area.</p>
  </div>
</div>
<!-- END OF PRICING SECTION 
<section id="ourClients" class="our-clients">
  <div class="container max-w-7xl">
    <div class="sec-heading text-center">
      <h2 class="about-heading">Our Partners</h2>
    </div>
    <div class="owl-carousel client-logos-slider">
      <div class="item">
        <figure class="logo-card"><img src="{{asset('themes/'.$theme->folder.'/images/partners/logo1.png')}}"></figure>
      </div>
      <div class="item">
        <figure class="logo-card"><img src="{{asset('themes/'.$theme->folder.'/images/partners/logo2.png')}}"></figure>
      </div>
      <div class="item">
        <figure class="logo-card"><img src="{{asset('themes/'.$theme->folder.'/images/partners/logo3.png')}}"></figure>
      </div>
      <div class="item">
        <figure class="logo-card"><img src="{{asset('themes/'.$theme->folder.'/images/partners/logo4.png')}}"></figure>
      </div>
      <div class="item">
        <figure class="logo-card"><img src="{{asset('themes/'.$theme->folder.'/images/partners/logo5.png')}}"></figure>
      </div>
      <div class="item">
        <figure class="logo-card"><img src="{{asset('themes/'.$theme->folder.'/images/partners/logo6.png')}}"></figure>
      </div>
      <div class="item">
        <figure class="logo-card"><img src="{{asset('themes/'.$theme->folder.'/images/partners/logo7.png')}}"></figure>
      </div>
      <div class="item">
        <figure class="logo-card"><img src="{{asset('themes/'.$theme->folder.'/images/partners/logo8.png')}}"></figure>
      </div>
      <div class="item">
        <figure class="logo-card"><img src="{{asset('themes/'.$theme->folder.'/images/partners/logo9.png')}}"></figure>
      </div>
      <!-- <div class="item"><figure class="logo-card"><img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/557257/6.png"></figure></div> 
    </div>
  </div>
</section> -->

<div class="slider">
    <div class="sec-heading text-center">
      <h2 class="about-heading">Our <span>Partners</span></h2>
    </div>
	<div class="slide-track">
		<div class="slide">
			<img src="{{asset('themes/'.$theme->folder.'/images/partners/logo1.png')}}" height="100" width="250" alt="" />
		</div>
		<div class="slide">
			<img src="{{asset('themes/'.$theme->folder.'/images/partners/logo2.png')}}" height="100" width="250" alt="" />
		</div>
		<div class="slide">
			<img src="{{asset('themes/'.$theme->folder.'/images/partners/logo3.png')}}" height="100" width="250" alt="" />
		</div>
		<div class="slide">
			<img src="{{asset('themes/'.$theme->folder.'/images/partners/logo4.png')}}" height="100" width="250" alt="" />
		</div>
		<div class="slide">
			<img src="{{asset('themes/'.$theme->folder.'/images/partners/logo5.png')}}" height="100" width="250" alt="" />
		</div>
		<div class="slide">
			<img src="{{asset('themes/'.$theme->folder.'/images/partners/logo6.png')}}" height="100" width="250" alt="" />
		</div>
		<div class="slide">
			<img src="{{asset('themes/'.$theme->folder.'/images/partners/logo7.png')}}" height="100" width="250" alt="" />
		</div>
		<div class="slide">
			<img src="{{asset('themes/'.$theme->folder.'/images/partners/logo8.png')}}" height="100" width="250" alt="" />
		</div>
		<div class="slide">
			<img src="{{asset('themes/'.$theme->folder.'/images/partners/logo9.png')}}" height="100" width="250" alt="" />
		</div>
	</div>
</div>


@endsection
@section('javascript')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBvqndqKM903bY5xn03L2F0kFrL5B7_3vk&libraries=places" async defer></script>
  <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $(document).on('ready', function() {
$('.vc-track').on('click', function(){
    $(".inline_gallery").show();
})
		$('.vc-quote').on('click', function(){
    $(".inline_gallery").hide();
})

      $( "#from_address_show" ).click(function() {
        initAutocomplete('US','from');
      });
      $( "#to_address_show" ).click(function() {
        initAutocomplete('US','to');
      });
      $(".tracking_link").on("click", function(e){
        e.preventDefault();
        var tracking_number = $("#tracking_number").val();
        if (tracking_number == '') {
          $(".result_not_found").html("<p>Please enter tracking number</p>");
          $("#tracking_number").focus();
          $('.tracking_link').attr('href', 'javascript:void(0)');
          return false;
        }
        $.ajax({
          type: "POST",
          url: '/validate-tracking',
          data: {'shipped_trackingNumber' : tracking_number},
          dataType: 'JSON',
          success: function( response ) {
            if (response.status == 'error') {
              $(".result_not_found").html("<p>"+response.package_information+"</p>");
              $('.tracking_link').attr('href', 'javascript:void(0)');
            }else{
              $(".result_not_found").html("");
              window.location.href = 'track-package/'+tracking_number;
            }
          }
        });
      })
      $.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		    }
		});
		var room = <?=(isset($quote_request['package_count']) && !empty($quote_request['package_count']))?$quote_request['package_count']:1?>;
      	var componentForm = {
		  	//street_number: 'short_name',
		  	//route: 'long_name',
		  	from_city: 'long_name',
		  	from_state: 'short_name',
		  	from_zip: 'short_name'
		};
		var to_componentForm = {
		  	//street_number: 'short_name',
		  	//route: 'long_name',
		  	to_city: 'long_name',
		  	to_state: 'short_name',
		  	to_zip: 'short_name'
		};
		function initAutocomplete(country='US', address_type='from') {
			//console.log(country, 888)
		  	// Create the autocomplete object, restricting the search predictions to
		  	// geographical location types.
		  	autocomplete = new google.maps.places.Autocomplete(
		  		document.getElementById(address_type+'_address_show'), {types: ['geocode']});

		  	// console.log("autocomplete::"+JSON.stringify(autocomplete));
      if(address_type == 'from'){
			  autocomplete.setComponentRestrictions({'country': country});
      } 
			// Avoid paying for data that you don't need by restricting the set of
		  	// place fields that are returned to just the address components.
		  	autocomplete.setFields(['address_component']);
			// When the user selects an address from the drop-down, populate the
		  	// address fields in the form.
		  	if (address_type == 'from') {
				autocomplete.addListener('place_changed', fillInAddress);
		  	}else{
		  		autocomplete.addListener('place_changed', fillInToAddress);
			}
		}

		function fillInAddress() {
			// Get the place details from the autocomplete object.
		  	var place = autocomplete.getPlace();
			
			//console.log("place::"+JSON.stringify(place));

			for (var component in componentForm) {
				//console.log("component::"+component);

		    	document.getElementById(component).value = '';
		    	document.getElementById(component).disabled = false;
		  	}

			// Get each component of the address from the place details,
			// and then fill-in the corresponding field on the form.
		  	for (var i = 0; i < place.address_components.length; i++) {
				var addressType = place.address_components[i].types[0];
				if(place.address_components[i].types[0] == 'administrative_area_level_1') {
		    		var addressType = 'from_state';
				}
				if(place.address_components[i].types[0] == 'locality') {
		    		var addressType = 'from_city';
				}
				if(place.address_components[i].types[0] == 'postal_code') {
		    		var addressType = 'from_zip';
				}
        if(place.address_components[i].types[0] == 'country') {
		    		var fromCountry = place.address_components[i].long_name;
				}
				if (componentForm[addressType]) {
		      		var val = place.address_components[i][componentForm[addressType]];
		      		document.getElementById(addressType).value = val;
		    	}
		  	}
		  	document.getElementById('from_address').value = place.address_components[0]['short_name'] + ' ' + place.address_components[1]['short_name'];
        document.getElementById('from_address_show').value = place.address_components[0]['short_name'] + ' ' + place.address_components[1]['short_name']+' '+document.getElementById('from_city').value+' '+document.getElementById('from_state').value+' '+document.getElementById('from_zip').value+' '+fromCountry;
		}

		function fillInToAddress() {
			// Get the place details from the autocomplete object.
		  	var place = autocomplete.getPlace();
			
			//console.log("place::"+JSON.stringify(place));

			for (var component in to_componentForm) {
				//console.log("component::"+component);

		    	document.getElementById(component).value = '';
		    	document.getElementById(component).disabled = false;
		  	}

			// Get each component of the address from the place details,
			// and then fill-in the corresponding field on the form.
		  	for (var i = 0; i < place.address_components.length; i++) {
				var addressType = place.address_components[i].types[0];
				if(place.address_components[i].types[0] == 'administrative_area_level_1') {
		    		var addressType = 'to_state';
				}
				if(place.address_components[i].types[0] == 'locality') {
		    		var addressType = 'to_city';
				}
				if(place.address_components[i].types[0] == 'postal_code') {
		    		var addressType = 'to_zip';
				}
        if(place.address_components[i].types[0] == 'country') {
		    		var toCountry = place.address_components[i].long_name;
            var toCountry_short = place.address_components[i].short_name;
            document.getElementById('to_country').value = toCountry_short.toUpperCase();
				}
				if (to_componentForm[addressType]) {
		      		var val = place.address_components[i][to_componentForm[addressType]];
		      		document.getElementById(addressType).value = val;
		    	}
		  	}
        document.getElementById('to_address').value = place.address_components[0]['short_name'] + ' ' + place.address_components[1]['short_name'];
		  	document.getElementById('to_address_show').value = place.address_components[0]['short_name'] + ' ' + place.address_components[1]['short_name']+' '+document.getElementById('to_city').value+' '+document.getElementById('to_state').value+' '+document.getElementById('to_zip').value+' '+toCountry;
		}

		$(document).ready(function(){
			$(".msDropdown").msDropdown();

			$("#flatrate_checkbox").on("change", function(){
				$("#package_count").trigger("change");
				if(this.checked) {
			        $("#Shipment_type").html('<div class="col-lg-12"><div class="form-group"><select class="form-control form-select disable_delivery_info required" required name="shipment_type" id="Shipment_type_dropdown"><option disabled selected value> -- Select Shipment Type -- </option><option value="contains_document">Document</option></select></div></div>');
				}else{
			    	$("#Shipment_type").html('');
			    }
			})

			$("#package_count").on("change", function(){
				var weight = length = width = height = readonly = '';
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'contains_document') {
					weight = 0.5;
					length = 12;
					width  = 8;
					height = 1;
					readonly = 'readonly';
				}
				var objTo = document.getElementById('education_fields');
				var divtest = '';
				room = $(this).val();

				for (var i = 1; i <= room; i++) {
					divtest += '<li class="removeclass'+i+'"><div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions['+"weight"+'][]" placeholder="Weight in lbs" value="'+weight+'" '+readonly+'></div><div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions['+"length"+'][]" placeholder="Length" value="'+length+'" '+readonly+'></div><div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions['+"width"+'][]" placeholder="Width" value="'+width+'" '+readonly+'></div><div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions['+"height"+'][]" placeholder="Height" value="'+height+'" '+readonly+'></div>';

			    	if (i == 1) {
			    		divtest += '<button class="btn btn-success" type="button" onclick="education_fields();"><i class="fas fa-plus"></i> </button>';
			    	}else{
			    		divtest += '<button class="btn btn-danger" type="button" onclick="remove_education_fields('+ i +');"> <i class="fas fa-trash-alt"></i> </button>';
			    	}
			    	divtest += '</li>';
				}

			    console.log("divtest::"+divtest);

			    objTo.innerHTML = divtest;	
			})

			$("form#get_quote_form").on("submit", function(e){
				e.preventDefault();
				$.ajax({
		           	type: "POST",
		           	url: '/set-quote-form',
		           	data: $(this).serialize(),
		           	dataType: 'JSON',
					success: function( response ) {
                  window.location.href = "/get-quote";
	                }
		       });
			})
		})

		// Form js
       	function education_fields() {
       		var weight = length = width = height = readonly = '';
			if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'contains_document') {
				weight = 1;
				length = 12;
				width  = 9;
				height = 1;
				readonly = 'readonly';
			}
			room++;
		    var objTo = document.getElementById('education_fields')
		    var divtest = document.createElement("li");
		    divtest.setAttribute("class", " removeclass"+room);
		    var rdiv = 'removeclass'+room;
		    divtest.innerHTML = '<div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions['+"weight"+'][]" placeholder="Weight in lbs" value="'+weight+'" '+readonly+'></div><div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions['+"length"+'][]" placeholder="Length" value="'+length+'" '+readonly+'></div><div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions['+"width"+'][]"  value="'+width+'" '+readonly+' placeholder="Width"></div><div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions['+"height"+'][]" placeholder="Height" value="'+height+'" '+readonly+'></div><button class="btn btn-danger" type="button" onclick="remove_education_fields('+ room +');"> <i class="fas fa-trash-alt"></i> </button>';
		    
		    objTo.appendChild(divtest);
		    $("#package_count").val(room);
		}
		function remove_education_fields(rid) {
			$('.removeclass'+rid).remove();
			room--;
			$("#package_count").val(room);
		}

		$(document).on("click change keyup", '.disable_delivery_info', function(){
			$(".delivery_information").html("");	
		})

		$(document).on("click", ".actiavted_toggle", function(){
			$(this).removeClass("activelink");
			var tag = $(this).data('tag');
			$('#'+tag).addClass('hide').removeClass('active');
			$(this).find('i').attr("class", "fas fa-chevron-down");
			$(this).attr("class", "del-toggle");
		})

		$(document).on("click", ".del-toggle", function(){
		    $('.del-toggle').removeClass('activelink');
            $(this).addClass('activelink');
            $('.actiavted_toggle').find('i').attr("class", "fas fa-chevron-down");
            var tagid = $(this).data('tag');
            $('.rate-listing').removeClass('active').addClass('hide');
            $('#'+tagid).addClass('active').removeClass('hide');
        	$(this).find('i').attr("class", "fas fa-chevron-up");
        	$(this).attr("class", "actiavted_toggle");
        });

        $(document).on("change", "#Shipment_type_dropdown", function(e){
        	e.preventDefault();
        	$("#package_count").trigger("change");
		})

        $(document).on("click", ".do_ship", function(e){
        	e.preventDefault();
        	var delivery_location = $(".delivery_location").val();
        	if (delivery_location === "" || delivery_location === null || delivery_location === undefined) {
        		$(".delivery_location").focus();
        		return false;
        	}else{
        		var url = $(this).data('url');
        		window.location.href = url;
        		return true;
        	}
        })
    });
		
		window.addEventListener('DOMContentLoaded', (event) => {
particlesJS('particles-js',
				{
					"particles": {
						"number": {
							"value": 80,
							"density": {
								"enable": true,
								"value_area": 800
							}
						},
						"color": {
							"value": "#ffffff"
						},
						"shape": {
							"type": "circle",
							"stroke": {
								"width": 0,
								"color": "#000000"
							},
							"polygon": {
								"nb_sides": 5
							},
							"image": {
								"src": "img/github.svg",
								"width": 100,
								"height": 100
							}
						},
						"opacity": {
							"value": 0.5,
							"random": false,
							"anim": {
								"enable": false,
								"speed": 1,
								"opacity_min": 0.1,
								"sync": false
							}
						},
						"size": {
							"value": 5,
							"random": true,
							"anim": {
								"enable": false,
								"speed": 40,
								"size_min": 0.1,
								"sync": false
							}
						},
						"line_linked": {
							"enable": true,
							"distance": 150,
							"color": "#ffffff",
							"opacity": 0.4,
							"width": 1
						},
						"move": {
							"enable": true,
							"speed": 6,
							"direction": "none",
							"random": false,
							"straight": false,
							"out_mode": "out",
							"attract": {
								"enable": false,
								"rotateX": 600,
								"rotateY": 1200
							}
						}
					},
					"interactivity": {
						"detect_on": "canvas",
						"events": {
							"onhover": {
								"enable": true,
								"mode": "repulse"
							},
							"onclick": {
								"enable": true,
								"mode": "push"
							},
							"resize": true
						},
						"modes": {
							"grab": {
								"distance": 400,
								"line_linked": {
									"opacity": 1
								}
							},
							"bubble": {
								"distance": 400,
								"size": 40,
								"duration": 2,
								"opacity": 8,
								"speed": 3
							},
							"repulse": {
								"distance": 200
							},
							"push": {
								"particles_nb": 4
							},
							"remove": {
								"particles_nb": 2
							}
						}
					},
					"retina_detect": true,
					"config_demo": {
						"hide_card": false,
						"background_color": "#b61924",
						"background_image": "",
						"background_position": "50% 50%",
						"background_repeat": "no-repeat",
						"background_size": "cover"
					}
				}

			)
});

	
  </script>
  
  
		<script>
		
		/* JS for mobile navigation */
		(function($) { // Begin jQuery
		  $(function() { // DOM ready
			// If a link has a dropdown, add sub menu toggle.
			$('.mobile-nav ul li a:not(:only-child)').click(function(e) {
			  $(this).siblings('.nav-dropdown').toggle();
			  // Close one dropdown when selecting another
			  $('.nav-dropdown').not($(this).siblings()).hide();
			  e.stopPropagation();
			});
			// Clicking away from dropdown will remove the dropdown class
			$('html').click(function() {
			  $('.mobile-nav .nav-dropdown').hide();
			});
			// Toggle open and close nav styles on click
			$('#nav-toggle').click(function() {
			  $('.mobile-nav ul').slideToggle();
			});
			// Hamburger to X toggle
			$('#nav-toggle').on('click', function() {
			  this.classList.toggle('active');
			});
		  }); // end DOM ready
		})(jQuery); // end jQuery
		
		</script>
@endsection
