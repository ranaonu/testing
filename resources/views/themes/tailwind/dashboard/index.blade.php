@extends('theme::layouts.app')


@section('content')
<style>
	.color{color:#6d4bef;}
	p{
		padding:5px 0px;
	}
</style>
	<section class="welcome-page">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2">
				<div class="welcome-row text-center">
					<div class="sec-heading welcome-text text-center">
						<div class="welcome-head">
							<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
							  <circle class="path circle" fill="none" stroke="#ffffff" stroke-width="6" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1"/>
							  <polyline class="path check" fill="none" stroke="#ffffff" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" points="100.2,40.2 51.5,88.8 29.8,67.5 "/>
							</svg>
							<h2 class="mt-2 welcome-main-heading">Welcome <span>{{ Auth::user()->name }}</span></h2>
							<p class="welcome-main-para">Congratulations {{ Auth::user()->name }}, <br> Thank you for creating your account with <span>Zion Shipping!</span></p>
							<div class="arrow-down"></div>
						</div>	
					<div class="welcome-content">
						<p class="mt-2 text-base text-gray-600 code-wp">Your Zion Shipping Express account number is: <span class="color">ZSE{{ Auth::user()->id }}</span></p>
						<div class="welcome-hr"></div>
						<h2 class="welcome-heading w-full my-1 text-base text-left text-gray-900 opacity-75 sm:my-2 sm:text-center">You have benefited from a free private address:</h2>
						<p class="address-text">
							1117 NE 163rd ST Ste C-ZSE57281, North Miami Beach FL 33162</br>
							<span>PHONE :</span> 305 515 2616</br>
						</p>
						<p class="mt-2 text-base text-gray-600 notice-text d-none"><span>NB </span> : Never forget to add your personal code ZSE{{ Auth::user()->id }} in the address. It is with this code that the office will <br>identify your packages. Memorize or write it down, because you will not be allowed to create another account.</p>
					</div>
					</div>
					<div class="welcome-text d-none">
						
						<p class="mt-2 text-base text-gray-600">We are available 7 days a week, 24 hours a day on WhatsApp. </p>
						<a class="btn btn-success" href="https://wa.me/19062144627" target="_blank">
						<i class="fa fa-whatsapp" aria-hidden="true"></i>
						Click here for immediate help
					  </a>
						<p class="account-button">Click <a href="/setting/profile"><strong style="color: #0067fb;"><u>Here</u></strong></a> to view your account: <a href="/setting/profile"><strong style="color: #0067fb;"><u>View My Account</u></strong></a></p>	
						<div class="welcome-hr"></div>
						<p class="mt-2 text-base text-gray-600 notice-text"><span>Click here to calculate the price of your shipment in advance </span> :
						With a Commercial Account , you can benefit from up to 40% discount <br>on your shipments and orders and many other advantages. Upgrade your account to reduce on your payments.</p>
					</div>	
				</div>
			</div>
		</div>	
	</div><!-- container-->
	<a href="https://wa.me/19062144627" class="float" target="_blank">
		<i class="fa fa-whatsapp my-float"></i>
	</a>
	</section>
	<!-- <div class="flex flex-col px-8 mx-auto my-6 lg:flex-row max-w-7xl xl:px-5">
	    <div class="flex flex-col justify-start flex-1 mb-5 overflow-hidden bg-white border rounded-lg lg:mr-3 lg:mb-0 border-gray-150">
	        <div class="flex flex-wrap items-center justify-between p-5 bg-white border-b border-gray-150 sm:flex-no-wrap">
				<div class="flex items-center justify-center w-12 h-12 mr-5 rounded-lg bg-wave-100">
					<svg class="w-6 h-6 text-wave-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
				</div>
				<div class="relative flex-1">
	                <h3 class="text-lg font-medium leading-6 text-gray-700">
	                    Welcome to your Dashboard
	                </h3>
	                <p class="text-sm leading-5 text-gray-500 mt">
	                    Learn More Below
	                </p>
				</div>

	        </div>
	        <div class="relative p-5">
	            <p class="text-base leading-loose text-gray-500">This is your application <a href="{{ route('wave.dashboard') }}" class="underline text-wave-500">dashboard</a>, you can customize this view inside of <code class="px-2 py-1 font-mono text-base font-medium text-gray-600 bg-gray-100 rounded-md">{{ theme_folder('/dashboard/index.blade.php') }}</code><br><br> (Themes are located inside the <code>resources/views/themes</code> folder)</p>
				<span class="inline-flex mt-5 rounded-md shadow-sm">
	                <a href="{{ url('docs') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-700 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50">
	                    Read The Docs
	                </a>
				</span>
			</div>
		</div>
		<div class="flex flex-col justify-start flex-1 overflow-hidden bg-white border rounded-lg lg:ml-3 border-gray-150">
	        <div class="flex flex-wrap items-center justify-between p-5 bg-white border-b border-gray-150 sm:flex-no-wrap">
				<div class="flex items-center justify-center w-12 h-12 mr-5 rounded-lg bg-wave-100">
					<svg class="w-6 h-6 text-wave-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg>
				</div>
				<div class="relative flex-1">
	                <h3 class="text-lg font-medium leading-6 text-gray-700">
						Learn more about Wave
	                </h3>
	                <p class="text-sm leading-5 text-gray-500 mt">
						Are you more of a visual learner?
	                </p>
				</div>

	        </div>
	        <div class="relative p-5">
				<p class="text-base leading-loose text-gray-500">Make sure to head on over to the Wave Video Tutorials to learn more how to use and customize Wave.<br><br>Click on the button below to checkout the video tutorials.</p>
				<span class="inline-flex mt-5 rounded-md shadow-sm">
	                <a href="https://devdojo.com/course/wave" target="_blank" class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-700 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50">
						Watch The Videos
	                </a>
				</span>
			</div>
	    </div>

	</div> -->

@endsection
