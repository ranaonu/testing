@extends('theme::layouts.app')

@section('content')

	<div class="flex px-8 mx-auto my-6 max-w-7xl xl:px-5">

		<!-- Left Settings Menu -->
		<div class="w-16 mr-6 md:w-1/5">

			<div class="relative flex flex-col items-start justify-center w-full py-6 bg-white border rounded-lg border-gray-150">
				<h3 class="hidden px-6 pb-3 text-xs font-semibold leading-4 tracking-wider text-gray-500 uppercase md:block">Settings</h3>

				<a href="{{ route('wave.settings', 'profile') }}" class="block relative w-full flex items-center px-6 py-3 text-sm font-medium leading-5 @if(Request::is('settings/profile')){{ 'text-gray-900' }}@else{{ 'text-gray-600' }}@endif transition duration-150 ease-in-out rounded-md group hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:text-gray-900 focus:bg-gray-50">
					<svg class="flex-shrink-0 w-5 h-5 mr-3 -ml-1 @if(Request::is('settings/profile')){{ 'text-gray-500' }}@else{{ 'text-gray-400' }}@endif transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
					<span class="hidden truncate md:inline-block">Profile</span>
					<span class="absolute left-0 block w-1 transition-all duration-300 ease-out rounded-full @if(Request::is('settings/profile')){{ 'bg-wave-500 h-full top-0' }}@else{{ 'top-1/2 bg-gray-300 group-hover:top-0 h-0 group-hover:h-full' }}@endif "></span>
				</a>
				<a href="{{ route('wave.settings', 'security') }}" class="block relative w-full flex items-center px-6 py-3 text-sm font-medium leading-5 @if(Request::is('settings/security')){{ 'text-gray-900' }}@else{{ 'text-gray-600' }}@endif transition duration-150 ease-in-out rounded-md group hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:text-gray-900 focus:bg-gray-50">
					<svg class="flex-shrink-0 w-5 h-5 mr-3 -ml-1 @if(Request::is('settings/security')){{ 'text-gray-500' }}@else{{ 'text-gray-400' }}@endif transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
					<span class="hidden truncate md:inline-block">Security</span>
					<span class="absolute left-0 block w-1 transition-all duration-300 ease-out rounded-full @if(Request::is('settings/security')){{ 'bg-wave-500 h-full top-0' }}@else{{ 'top-1/2 bg-gray-300 group-hover:top-0 h-0 group-hover:h-full' }}@endif "></span>
				</a>
				<a href="{{ route('wave.settings', 'api') }}" class="block relative w-full flex items-center px-6 py-3 text-sm font-medium leading-5 @if(Request::is('settings/api')){{ 'text-gray-900' }}@else{{ 'text-gray-600' }}@endif transition duration-150 ease-in-out rounded-md group hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:text-gray-900 focus:bg-gray-50">
				<img class="flex-shrink-0 w-5 h-5 mr-3 -ml-1 @if(Request::is('settings/security')){{ 'text-gray-500' }}@else{{ 'text-gray-400' }}@endif transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-500" src="{{url('/themes/tailwind/images/Languages.svg')}}"/>
					<span class="hidden truncate md:inline-block">Languages</span>
					<span class="absolute left-0 block w-1 transition-all duration-300 ease-out rounded-full @if(Request::is('settings/api')){{ 'bg-wave-500 h-full top-0' }}@else{{ 'top-1/2 bg-gray-300 group-hover:top-0 h-0 group-hover:h-full' }}@endif "></span>
				</a>
			</div>

			<div class="relative flex flex-col items-start justify-center w-full py-6 mt-6 bg-white border rounded-lg border-gray-150">
				<h3 class="hidden px-6 pb-3 text-xs font-semibold leading-4 tracking-wider text-gray-500 uppercase md:block">Billing</h3>

				<a href="{{ route('wave.settings', 'plans') }}" style""" class="block relative w-full flex items-center px-6 py-3 text-sm font-medium leading-5 @if(Request::is('settings/plans')){{ 'text-gray-900' }}@else{{ 'text-gray-600' }}@endif transition duration-150 ease-in-out rounded-md group hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:text-gray-900 focus:bg-gray-50">
					<svg class="flex-shrink-0 w-5 h-5 mr-3 -ml-1 @if(Request::is('settings/plans')){{ 'text-gray-500' }}@else{{ 'text-gray-400' }}@endif transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
					<span class="hidden truncate md:inline-block">Plans</span>
					<span class="absolute left-0 block w-1 transition-all duration-300 ease-out rounded-full @if(Request::is('settings/plans')){{ 'bg-wave-500 h-full top-0' }}@else{{ 'top-1/2 bg-gray-300 group-hover:top-0 h-0 group-hover:h-full' }}@endif "></span>
				</a>
				<a href="{{ route('wave.settings', 'subscription') }}" class="block relative w-full flex items-center px-6 py-3 text-sm font-medium leading-5 @if(Request::is('settings/payment-information')){{ 'text-gray-900' }}@else{{ 'text-gray-600' }}@endif transition duration-150 ease-in-out rounded-md group hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:text-gray-900 focus:bg-gray-50">
					<svg class="flex-shrink-0 w-5 h-5 mr-3 -ml-1 @if(Request::is('settings/subscription')){{ 'text-gray-500' }}@else{{ 'text-gray-400' }}@endif transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
					<span class="hidden truncate md:inline-block">Subscription</span>
					<span class="absolute left-0 block w-1 transition-all duration-300 ease-out rounded-full @if(Request::is('settings/subscription')){{ 'bg-wave-500 h-full top-0' }}@else{{ 'top-1/2 bg-gray-300 group-hover:top-0 h-0 group-hover:h-full' }}@endif "></span>
				</a>
				<a href="{{ route('wave.settings', 'cards') }}" class="block relative w-full flex items-center px-6 py-3 text-sm font-medium leading-5 @if(Request::is('settings/cards')){{ 'text-gray-900' }}@else{{ 'text-gray-600' }}@endif transition duration-150 ease-in-out rounded-md group hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:text-gray-900 focus:bg-gray-50">
					<svg class="flex-shrink-0 w-5 h-5 mr-3 -ml-1 @if(Request::is('settings/cards')){{ 'text-gray-500' }}@else{{ 'text-gray-400' }}@endif transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
					<span class="hidden truncate md:inline-block">Cards</span>
					<span class="absolute left-0 block w-1 transition-all duration-300 ease-out rounded-full @if(Request::is('settings/cards')){{ 'bg-wave-500 h-full top-0' }}@else{{ 'top-1/2 bg-gray-300 group-hover:top-0 h-0 group-hover:h-full' }}@endif "></span>
				</a>
				<a href="{{ route('wave.settings', 'order-history') }}" class="block relative w-full flex items-center px-6 py-3 text-sm font-medium leading-5 @if(Request::is('settings/order-history')){{ 'text-gray-900' }}@else{{ 'text-gray-600' }}@endif transition duration-150 ease-in-out rounded-md group hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:text-gray-900 focus:bg-gray-50">
					<svg class="flex-shrink-0 w-5 h-5 mr-3 -ml-1 @if(Request::is('settings/order-history')){{ 'text-gray-500' }}@else{{ 'text-gray-400' }}@endif transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
					<span class="hidden truncate md:inline-block">Order History</span>
					<span class="absolute left-0 block w-1 transition-all duration-300 ease-out rounded-full @if(Request::is('settings/order-history')){{ 'bg-wave-500 h-full top-0' }}@else{{ 'top-1/2 bg-gray-300 group-hover:top-0 h-0 group-hover:h-full' }}@endif "></span>
				</a>
			</div>

			<div class="relative flex flex-col items-start justify-center w-full py-6 mt-6 bg-white border rounded-lg border-gray-150">
				<h3 class="hidden px-6 pb-3 text-xs font-semibold leading-4 tracking-wider text-gray-500 uppercase md:block">Support</h3>

				
				<a href="{{ route('wave.settings', 'claims') }}" class="block relative w-full flex items-center px-6 py-3 text-sm font-medium leading-5 @if(Request::is('settings/claims')){{ 'text-gray-900' }}@else{{ 'text-gray-600' }}@endif transition duration-150 ease-in-out rounded-md group hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:text-gray-900 focus:bg-gray-50">
					<svg class="flex-shrink-0 w-5 h-5 mr-3 -ml-1 @if(Request::is('settings/cards')){{ 'text-gray-500' }}@else{{ 'text-gray-400' }}@endif transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
					<span class="hidden truncate md:inline-block">My Claims</span>
					<span class="absolute left-0 block w-1 transition-all duration-300 ease-out rounded-full @if(Request::is('settings/claims')){{ 'bg-wave-500 h-full top-0' }}@else{{ 'top-1/2 bg-gray-300 group-hover:top-0 h-0 group-hover:h-full' }}@endif "></span>
				</a>
				<a href="{{ route('wave.settings', 'tickets') }}" class="block relative w-full flex items-center px-6 py-3 text-sm font-medium leading-5 @if(Request::is('settings/tickets')){{ 'text-gray-900' }}@else{{ 'text-gray-600' }}@endif transition duration-150 ease-in-out rounded-md group hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:text-gray-900 focus:bg-gray-50">
					<svg class="flex-shrink-0 w-5 h-5 mr-3 -ml-1 @if(Request::is('settings/order-history')){{ 'text-gray-500' }}@else{{ 'text-gray-400' }}@endif transition duration-150 ease-in-out group-hover:text-gray-500 group-focus:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
					<span class="hidden truncate md:inline-block">My Tickets</span>
					<span class="absolute left-0 block w-1 transition-all duration-300 ease-out rounded-full @if(Request::is('settings/tickets')){{ 'bg-wave-500 h-full top-0' }}@else{{ 'top-1/2 bg-gray-300 group-hover:top-0 h-0 group-hover:h-full' }}@endif "></span>
				</a>
			</div>

		</div>
		<!-- End Settings Menu -->

		<div class="flex flex-col w-full bg-white border rounded-lg md:w-4/5 border-gray-150">
			<div class="flex flex-wrap items-center justify-between border-b border-gray-200 sm:flex-no-wrap">
	            <div class="relative p-6">
	                <h3 class="flex text-lg font-medium leading-6 text-gray-600">
						<svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
						@if(isset($section_title)){{ $section_title }}@else{{ Auth::user()->name . '\'s' }} {{ ucwords(str_replace('-', ' ', Request::segment(2)) ?? 'profile') . ' Settings' }}@endif
	                </h3>
	            </div>
	        </div>
			<div class="uk-card-body">
				@include('theme::settings.partials.' . $section)
			</div>
		</div>
	</div>

@endsection

@section('javascript')

	<style>
		#upload-crop-container .croppie-container .cr-resizer, #upload-crop-container .croppie-container .cr-viewport{
			box-shadow: 0 0 2000px 2000px rgba(255,255,255,1) !important;
			border: 0px !important;
		}
		.croppie-container .cr-boundary {
			border-radius: 50% !important;
			overflow: hidden;
		}
		.croppie-container .cr-slider-wrap{
			margin-bottom: 0px !important;
		}
		.croppie-container{
			height:auto !important;
		}
	</style>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/exif-js/2.3.0/exif.min.js"></script>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.min.css">
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.min.js"></script>
	<script>

			var uploadCropEl = document.getElementById('upload-crop');
			var uploadLoading = document.getElementById('uploadLoading');

			function readFile() {
				input = document.getElementById('upload');
				if (input.files && input.files[0]) {
					var reader = new FileReader();

					reader.onload = function (e) {
						//$('.upload-demo').addClass('ready');
						uploadCrop.bind({
							url: e.target.result,
							orientation: 4
						}).then(function(){
							//uploadCrop.setZoom(0);
						});
					}

					reader.readAsDataURL(input.files[0]);
				}
				else {
					alert("Sorry - you're browser doesn't support the FileReader API");
				}
			}

			if(document.getElementById('upload')){
				document.getElementById('upload').addEventListener('change', function () {
					document.getElementById('upload-modal').__x.$data.open = true;
					uploadCropEl.classList.add('hidden');
					uploadLoading.classList.remove('hidden');
					setTimeout(function(){
						uploadLoading.classList.add('hidden');
						uploadCropEl.classList.remove('hidden');

						if(typeof(uploadCrop) != "undefined"){
							uploadCrop.destroy();
						}
						uploadCrop = new Croppie(uploadCropEl, {
							viewport: { width: 190, height: 190, type: 'square' },
							boundary: { width: 190, height: 190 },
							enableExif: true,
						});

						readFile();
					}, 800);
				});
			}

			function clearInputField(){
				document.getElementById('upload').value = '';
			}

			function applyImageCrop(){
				uploadCrop.result({type:'base64',size:'original',format:'png',quality:1}).then(function(base64) {
					document.getElementById('preview').src = base64;
					document.getElementById('uploadBase64').value = base64;
				});
			}

	</script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBvqndqKM903bY5xn03L2F0kFrL5B7_3vk&libraries=places" async defer></script>
	<script>
		var current_section = '<?php echo $section;?>';
		if(current_section == 'profile'){
			var shipper_phone = document.querySelector("#shipper_phone");
			window.intlTelInput(shipper_phone, {
				allowDropdown: false, 
				initialCountry: "{{ Auth::user()->shipper_country }}",
				separateDialCode: true,
				utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
			});
		}
		var componentForm = {
			//street_number: 'short_name',
			//route: 'long_name',
			shipper_city: 'long_name',
			shipper_state: 'short_name',
			shipper_zip: 'short_name'
		};
		function getCountry(country, address_type='from') {
			if(country != undefined){
				window.intlTelInput(shipper_phone, {
					allowDropdown: false, 
					initialCountry: country,
					separateDialCode: true,
					utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
				});
				document.getElementById(address_type+'_address').value = '';
				document.getElementById(address_type+'_state').value = '';
				document.getElementById(address_type+'_city').value = '';
				document.getElementById(address_type+'_zip').value = '';
				initAutocomplete(country, address_type);
				if (address_type == 'from') {
					$("#from_country_name").val($("#from_country_code option:selected").text());
				}else{
					$("#to_country_name").val($("#to_country_code option:selected").text());
				}
			}  
		}
		function initAutocomplete(country, address_type='from') {
			//console.log(country, 888)
			// Create the autocomplete object, restricting the search predictions to
			// geographical location types.
			
			autocomplete = new google.maps.places.Autocomplete(
				document.getElementById(address_type+'_address'), {types: ['geocode']});

			//console.log("autocomplete::"+JSON.stringify(autocomplete));

			autocomplete.setComponentRestrictions({'country': country});
			// Avoid paying for data that you don't need by restricting the set of
			// place fields that are returned to just the address components.
			autocomplete.setFields(['address_component']);
			// When the user selects an address from the drop-down, populate the
			// address fields in the form.
			if (address_type == 'shipper') {
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
					var addressType = 'shipper_state';
				}
				if(place.address_components[i].types[0] == 'locality') {
					var addressType = 'shipper_city';
				}
				if(place.address_components[i].types[0] == 'postal_code') {
					var addressType = 'shipper_zip';
				}
				if (componentForm[addressType]) {
					var val = place.address_components[i][componentForm[addressType]];
					document.getElementById(addressType).value = val;
				}
			}
			document.getElementById('shipper_address').value = place.address_components[0]['short_name'] + ' ' + place.address_components[1]['short_name'];
		}
		$(document).ready(function(){
			$(".msDropdown").msDropdown();
		});

		$('#language').on('change', function (e) {
			$("#language_form").submit();
		});	
	</script>
@endsection
