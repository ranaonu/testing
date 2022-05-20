@extends('theme::layouts.app')

@section('content')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />
<main class="Shipping-layout">
	<div class="container max-w-7xl">
		<form class="get_quote_form" id="schedule_pickup">
			<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
			<div class="row">
				<div class="col-lg-12">
					<div class="form-card">
						<div class="form-head">
							<h3>Pickup Address </h3>
						</div>
						<div class="form-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label class="form-label">Name <span class="required">*</span></label>                                   
										<input type="text" class="form-control disable_delivery_info" required name="pickup_name" id="pickup_name" placeholder="">
									</div>
								</div>
								<!-- <div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Company <span class="required">*</span></label>                                   
										<input type="text" class="form-control disable_delivery_info" required name="pickup_company" id="pickup_company" placeholder="">
									</div>
								</div> -->
								<div class="col-lg-3">
									<div class="form-group">
										<label class="form-label">Country <span class="required">*</span></label>
										<input type="hidden" name="pickup_country_name" id="pickup_country_name" class="form-control disable_delivery_info" value="">
										<select class="form-control form-select disable_delivery_info msDropdown" required name="pickup_country" id="pickup_country_code" onChange="getCountry(this.value, 'pickup')">
											<option disabled selected value> -- Select country -- </option>
											@foreach ($countries as $country)
												<option value="{{$country->alpha_2_code}}" data-pickup_zip_supported={{$country->zip_code_supported}}  data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}">{{$country->country_name}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-lg-7">
									<div class="form-group">
										<label class="form-label">Address <span class="required">*</span></label>                                   
										<input type="text" class="form-control disable_delivery_info required" required name="pickup_address" id="pickup_address" placeholder="Enter a location" value="">
									</div>
								</div>
								<div class="col-lg-2">
									<div class="form-group">
										<label class="form-label">Apt/Ste/Unit<!-- <span class="required">*</span> --></label>                                   
										<input type="text" class="form-control disable_delivery_info required" name="pickup_apt" id="pickup_apt" placeholder="Enter a location" value="">
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label class="form-label" id="pickup_zip_code">Zip Code <span class="required">*</span></label>                       
										<input type="text" class="form-control disable_delivery_info required" required name="pickup_zip" id="pickup_zip" value="">
									</div>
								</div>
								<div class="col-lg-5">
									<div class="form-group">
										<label class="form-label">City <span class="required">*</span></label>                        
										<input type="text" class="form-control disable_delivery_info required" required name="pickup_city" id="pickup_city" value="">
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-label">State <span class="required">*</span></label>                       
										<input type="text" class="form-control disable_delivery_info required" required name="pickup_state" id="pickup_state"  value="">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Phone<span class="required">*</span></label>  
										<input type="number" class="form-control disable_delivery_info required" required id="pickup_phone" name="pickup_phone">                   
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Email <span class="required">*</span></label>                       
										<input type="email" class="form-control disable_delivery_info required" required id="pickup_email" name="pickup_email">
									</div>
								</div>
								<div class="col-lg-12">
									<div class="form-group">
										<label class="form-label">Pickup From <span class="required">*</span></label>                       
										<select name="pickup_from" id="pickup_from" class="form-control form-select pickup_from">
											<option value="" disabled selected>Select Carrier</option>
											<option value="dhl">DHL</option>
											<option value="ups">UPS</option>
											<option value="FedEx">FedEx</option>
											<option value="usps">USPS</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12" id="fedex_picup_options_container" style="display: none;">
									<div class="form-group">
										<label class="form-label">FedEx Pickup Types <span class="required">*</span></label>                       
										<select name="fedex_picup_options" id="fedex_picup_options" class="form-control form-select fedex_picup_options">
											<option value="" disabled selected>Select Pickup Type</option>
											<option value="express_pickup">Schedule a FedEx Express Pickup</option>
											<option value="ground_pickup">Schedule a FedEx Ground Pickup</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12" id="usps_picup_options_container" style="display: none;">
									<div class="form-group">
										<label class="form-label">USPS Pickup Types <span class="required">*</span></label>                       
										<select name="usps_picup_options" id="usps_picup_options" class="form-control form-select usps_picup_options">
											<option value="" disabled selected>Select Pickup Type</option>
											<option value="PriorityMailExpress">Priority Mail Express</option>
											<option value="PriorityMail">Priority Mail</option>
											<option value="ExpressMail">Express Mail</option>
											<option value="FirstClass">First Class</option>
											<option value="Returns">Returns</option>
											<option value="International">International</option>
										</select>
									</div>
								</div>

								<!-- <div class="col-lg-3">
									<div class="form-group">
										<label class="form-label">Phone type <span class="required">*</span></label>                       
										<select name="pickup_phone_type" id="pickup_phone_type" class="form-control form-select disable_delivery_info required" required>
											<option value="Mobile">Mobile</option>
											<option value="Office">Office</option>
											<option value="Other">Other</option>
										  </select>
									</div>
								</div> -->
								<!-- <div class="col-lg-3">
									<div class="form-group">
										<label class="form-label">Code<span class="required">*</span></label>  
										<input type="text" class="form-control disable_delivery_info required" required id="pickup_code" name="pickup_code">                   
									</div>
								</div> -->
								<!-- <div class="col-lg-3">
									<div class="form-group">
										<label class="form-label">Extension<span class="required">*</span></label>  
										<input type="text" class="form-control disable_delivery_info required" required id="pickup_extension" name="pickup_extension">                   
									</div>
								</div> -->
								<div class="col-lg-6" id="pickup_tracking_number_container">
									<div class="form-group">
										<label class="form-label">Tracking Number<span class="required">*</span></label>  
										<input type="text" class="form-control disable_delivery_info required" required id="pickup_tracking_number" name="pickup_tracking_number" placeholder="">                   
										<div class="alert alert-danger result_not_found"></div>
									</div>
								</div>
								<div class="col-lg-6" id="next_to_tracking_field">
									<div class="form-group">
										<label class="form-label">Where should the courier pick up the shipment? <span class="required">*</span></label>
										<select name="pick_location" id="pick_location" class="form-control form-select disable_delivery_info required" required>
											<option value="Reception">Reception</option>
											<option value="Back Door">Back Door</option>
											<option value="Front Door">Front Door</option>
											<option class="non-usps-specific" value="Loading Dock">Loading Dock</option>
											<option class="usps-specific" style="display:none;" value="Side Door">Side Door</option>
											<option class="usps-specific" style="display:none;" value="Knock on Door/Ring Bell">Knock on Door/Ring Bell</option>
											<option class="usps-specific" style="display:none;" value="Mail Room">Mail Room</option>
											<option class="usps-specific" style="display:none;" value="Office">Office</option>
											<option class="usps-specific" style="display:none;" value="In/At Mailbox">In/At Mailbox</option>
											<option value="Other">Other</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="form-group">
										<label class="form-label">Instructions for the courier <!-- <span class="required">*</span> --></label>                       
										<textarea type="text" class="form-control disable_delivery_info form-textarea" name="pickup_instruction" id="pickup_instruction" placeholder="Provide other instructions you'd like the courier to receive."></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-12">
					<div class="form-card">
						<div class="form-head">
							<h3>Package Information</h3>
						</div>
						<div class="form-body" id="package_information">
							<div class="row">
								<div class="col-lg-4">
									<label class="form-label">Flat rate items?</label>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" name="flat_rate" class="custom-control-input disable_delivery_info" id="flatrate_checkbox">
									</div>
								</div>
								<div class="col-lg-8">
									<div class="form-group">
										<label class="form-label">No. of Packages<span class="required">*</span></label>
										<select class="form-control form-select disable_delivery_info required" name="package_count" id="package_count">
											@for ($i = 1; $i <= 25; $i++)
										        <option value="{{ $i }}">{{ $i }}</option>
										    @endfor
										</select>
									</div>
								</div>
							</div>
							<div class="row" id="Shipment_type">
							</div>
							<div class="panel panel-default mb-4">
								<div class="panel-body">
									<ul id="education_fields" class="pkg-info-list">
										<li>
											<div class="form-group">
												<input type="text" class="form-control disable_delivery_info" required name="dimensions[weight][]" placeholder="Weight in lbs">
											</div>
											<div class="form-group">
												<input type="text" class="form-control disable_delivery_info" required name="dimensions[length][]" placeholder="Length in inches">
											</div>
											<div class="form-group">
												<input type="text" class="form-control disable_delivery_info" required name="dimensions[width][]" placeholder="Width in inches">
											</div>
											<div class="form-group">
												<input type="text" class="form-control disable_delivery_info" required name="dimensions[height][]" placeholder="Height in inches">
											</div>
											<button class="btn btn-success disable_delivery_info" type="button" onclick="education_fields();"> 
											<i class="fas fa-plus"></i> </button>
										</li>
									</ul>
								</div>
							</div>
							<div class="form-group">
								<label class="form-label">Total Value <span class="required">*</span></label>
								<input type="text" class="form-control disable_delivery_info" required name="total_value" placeholder="100" value="">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="form-card">
						<div class="form-head">
							<h3>When should we pickup your shipment? </h3>
						</div>
						<div class="form-body">
							<div class="row">
								<div class="col-lg-3">
						  			<label class="date-label">Pickup Date </label>
									<input  class="form-control disable_delivery_info" id="pickup_date" name="pickup_date">
								</div>
								<div class="col-lg-1"></div>
								<div class="col-lg-7">
									<input type="hidden" name="pickup_start_time" id="pickup_start_time" value="10:00 AM" class="form-control">
									<input type="hidden" name="pickup_end_time" id="pickup_end_time" value="05:00 PM" class="form-control">
									<h2 class="text-center">Pickup Window – When courier may arrive and shipment is ready </h2>
									<div id="time-range">
						  				<div class="sliders_step1">
											<div id="slider-range"></div>
											<div class="scale-img">
												<img src="http://dev.zionshipping.com/storage/themes/August2018/scale1.png"/>
											</div>
						  				</div>
									</div>
									<h2 class="text-center"> Please allow at least 60 minutes for your Pickup Window<br>
										The latest time a request can be made for pickup today is 6:30 pm </h2>
								</div>
								<div class="col-lg-1"></div>
							</div>
						</div>
					</div>	
				</div>
				<div class="btn-wrap text-center">
					<button type="submit" class="cstm-btn show_button">Schedule Pickup</button>
				</div>
			</div>
		</form>
		<div id="loader_image">
	        <img id="loading-image" src="{{ asset('themes/' . $theme->folder . '/images/loaders/searching.gif') }}" style="display:none; height: 500px!important;     margin-left: auto; margin-right: auto; width: 50%;"/>
	    </div>
		<div class="row delivery_information">
		</div>
	</div>
</main>
@endsection

@section('javascript')
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBvqndqKM903bY5xn03L2F0kFrL5B7_3vk&libraries=places" async defer></script>
	<script>
		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		    }
		});

		var c_minutes = pickup_from = '';
		var new_day = false;
			
		// current date
		const getDate = new Date();
		// format date to yyyy-mm-dd format
		const curDate = getDate.toISOString().split('T')[0];
		
		// const picker = document.getElementById('pickup_date');
		// picker.addEventListener('input', function(e){
		// 	var day = new Date(this.value).getUTCDay();
		// 	if([6,0].includes(day)){
		//     	e.preventDefault();
		// 		alert('Pickup is not available on weekends!');
		//   		// this.value = '';
		//   		initialize_slider();
		//   	}
		// });

		function formatAMPM(date) {
		  	var hours = date.getHours();
		  	var minutes = date.getMinutes();
		  	var ampm = hours >= 12 ? 'pm' : 'am';
		  	hours = hours % 12;
		  	hours = hours ? hours : 12; // the hour '0' should be '12'
		  	minutes = minutes < 10 ? '0'+minutes : minutes;
		  	var strTime = hours + ':' + minutes + ' ' + ampm;
		  	return strTime;
		}

		var update_next_30 = false;


		function get_minutes() {
		  	var dt = new Date();
			var hours = dt.getHours();
			var minutes = dt.getMinutes();
		  	var hours_in_minutes = parseInt(hours)*parseInt(60);
		  	var total_minutes = parseInt(hours_in_minutes) + parseInt(minutes);
		  	if (total_minutes % 30 !== 0) {
		  		total_minutes = Math.ceil(total_minutes / 30) * 30;
		  		update_next_30 = true;
			}
			return total_minutes;
		}

		var current_time = formatAMPM(new Date);
		
		var room = <?=(isset($quote_request['package_count']) && !empty($quote_request['package_count']))?$quote_request['package_count']:1?>;
      	var componentForm = {
		  	//street_number: 'short_name',
		  	//route: 'long_name',
		  	pickup_city: 'long_name',
		  	pickup_state: 'short_name',
		  	pickup_zip: 'short_name'
		};
		function getCountry(country, address_type='from') {
			if(country != undefined){
				document.getElementById(address_type+'_address').value = '';
				document.getElementById(address_type+'_state').value = '';
				document.getElementById(address_type+'_city').value = '';
				document.getElementById(address_type+'_zip').value = '';
				initAutocomplete(country, address_type);
				if (address_type == 'from') {
					$("#from_country_name").val($("#from_country_code option:selected").text());
				}else{
					$("#pickup_country_name").val($("#pickup_country_code option:selected").text());
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
		  	autocomplete.addListener('place_changed', fillInToAddress);
		}
		
		function fillInToAddress() {
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
		    		var addressType = 'pickup_state';
				}
				if(place.address_components[i].types[0] == 'locality') {
		    		var addressType = 'pickup_city';
				}
				if(place.address_components[i].types[0] == 'postal_code') {
		    		var addressType = 'pickup_zip';
				}
				if (componentForm[addressType]) {
		      		var val = place.address_components[i][componentForm[addressType]];
		      		document.getElementById(addressType).value = val;
		    	}
		  	}
		  	document.getElementById('pickup_address').value = place.address_components[0]['short_name'] + ' ' + place.address_components[1]['short_name'];
		}

		function get_phone_coode(country_code) {
			$(".iti__flag-container").remove();
			var pickup_phone = document.querySelector("#pickup_phone");
		    window.intlTelInput(pickup_phone, {
		      	allowDropdown: false, 
			  	initialCountry: country_code,
				separateDialCode: true,
		      	utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
		    });
		}
		function initialize_slider(passed_minutes='') {
			if (passed_minutes == '') {
				c_minutes = (get_minutes())?get_minutes():600
				if ((parseInt(1020) - parseInt(c_minutes)) <= 60 ) {
					
					c_minutes = 600;
					var next_date = new Date();
					next_date.setDate(next_date.getDate() + 1);
					var ad_month = parseInt(next_date.getMonth())+parseInt(1);
					var t_date = next_date.getFullYear()+"-"+ad_month+"-"+next_date.getDate();
					$("#pickup_date").val(t_date);
					// $("#pickup_date").attr('min', t_date);
					new_day = true;
				}else if(c_minutes < 600){
					c_minutes = 600;
					document.getElementById("pickup_date").setAttribute("value", curDate);
					// document.getElementById("pickup_date").setAttribute("min", curDate);
					new_day = true;
				}else{
					document.getElementById("pickup_date").setAttribute("value", curDate);
					// document.getElementById("pickup_date").setAttribute("min", curDate);
					new_day = false;
				}
			}else{
				c_minutes = passed_minutes;
				document.getElementById("pickup_date").setAttribute("value", curDate);
				// document.getElementById("pickup_date").setAttribute("min", curDate);
				new_day = false;
			}
			/* Time SLIDER START */
			$("#slider-range").slider({
				range: true,
				min: c_minutes,
				max: 1020,
				step: 30,
				values: [0, 1440],
				slide: function (e, ui) {
					var hours1 = Math.floor(ui.values[0] / 60);
					var minutes1 = ui.values[0] - (hours1 * 60);
					if (hours1.length == 1) hours1 = '0' + hours1;
					if (minutes1.length == 1) minutes1 = '0' + minutes1;
					if (minutes1 == 0) minutes1 = '00';
					
					var finale_minutes1 = minutes1;

					if (hours1 >= 12) {
						if (hours1 == 12) {
							hours1 = hours1;
							minutes1 = minutes1 + " PM";
						} else {
							hours1 = hours1 - 12;
							minutes1 = minutes1 + " PM";
						}
					} else {
						hours1 = hours1;
						minutes1 = minutes1 + " AM";
					}
					if (hours1 == 0) {
						hours1 = 12;
						minutes1 = minutes1;
					}

					var finale_hours1;
					if(minutes1.indexOf('PM') != -1 && hours1 < 12){
					    finale_hours1 = parseInt(12)+parseInt(hours1);
					}else{
						finale_hours1 = hours1;
					}

					var hours2 = Math.floor(ui.values[1] / 60);
					var minutes2 = ui.values[1] - (hours2 * 60);
					if (hours2.length == 1) hours2 = '0' + hours2;
					if (minutes2.length == 1) minutes2 = '0' + minutes2;
					if (minutes2 == 0) minutes2 = '00';

					var finale_minutes2 = minutes2;

					if (hours2 >= 12) {
						if (hours2 == 12) {
							hours2 = hours2;
							minutes2 = minutes2 + " PM";
						} else if (hours2 == 24) {
							hours2 = 11;
							minutes2 = "59 PM";
						} else {
							hours2 = hours2 - 12;
							minutes2 = minutes2 + " PM";
						}
					} else {
						hours2 = hours2;
						minutes2 = minutes2 + " AM";
					}

					var finale_hours2;
					if(minutes2.indexOf('PM') != -1 && hours2 < 12){
					    finale_hours2 = parseInt(12)+parseInt(hours2);
					}else{
						finale_hours2 = hours2;
					}

					const start_time = parseInt(finale_hours1) * parseInt(60) + parseInt(finale_minutes1);
					const end_time =  parseInt(finale_hours2) * parseInt(60) + parseInt(finale_minutes2);

					if (parseInt(end_time) - parseInt(start_time) < 60) {
						alert("Please allow at least 60 minutes for your Pickup Window!");
		            	return false;
					}

		            $('.sidecar1 small').html(hours1 + ':' + minutes1);
					$("#pickup_start_time").val(hours1 + ':' + minutes1);

					$('.sidecar2 small').html(hours2 + ':' + minutes2);
					$("#pickup_end_time").val(hours2 + ':' + minutes2);
				}
			});
			if (passed_minutes == '') {
				if (new_day) {
					$("#pickup_start_time").val('10:00 AM');
					$('.ui-slider-handle:first').html('<div class="sidecar1">Earliest <small>10:00 AM</small></div>');
				}else{
					$("#pickup_start_time").val(current_time);
					$('.ui-slider-handle:first').html('<div class="sidecar1">Earliest <small>'+current_time+'</small></div>');
				}
				$('.ui-slider-handle:last').html('<div class="sidecar2">Latest <small>05:00 PM</small></div>');
			}else{
				$("#pickup_start_time").val('10:00 AM');
				$('.ui-slider-handle:first').html('<div class="sidecar1">Earliest <small>10:00 AM</small></div>');
				$('.ui-slider-handle:last').html('<div class="sidecar2">Latest <small>05:00 PM</small></div>');
			}
			/* Time SLIDER END */
		}

		$(window).on('load', function() {
			if (update_next_30) {
				var c_hours1 = Math.floor(c_minutes / 60);
				var c_minutes1 = c_minutes - (c_hours1 * 60);
				if (c_hours1.length == 1) c_hours1 = '0' + c_hours1;
				if (c_minutes1.length == 1) c_minutes1 = '0' + c_minutes1;
				if (c_minutes1 == 0) c_minutes1 = '00';
				if (c_hours1 >= 12) {
					if (c_hours1 == 12) {
						c_hours1 = c_hours1;
						c_minutes1 = c_minutes1 + " PM";
					} else {
						c_hours1 = c_hours1 - 12;
						c_minutes1 = c_minutes1 + " PM";
					}
				} else {
					c_hours1 = c_hours1;
					c_minutes1 = c_minutes1 + " AM";
				}
				if (c_hours1 == 0) {
					c_hours1 = 12;
					c_minutes1 = c_minutes1;
				}
				$('.sidecar1 small').html(c_hours1 + ':' + c_minutes1);

				$("#pickup_start_time").val(c_hours1 + ':' + c_minutes1);
			}
		});

		$(document).ready(function(){
			initialize_slider();
			$("#pickup_from").on("change", function(){
				pickup_from = $(this).val();
				if (pickup_from == 'ups') {
					$("#next_to_tracking_field").attr("class", "col-lg-12");
					$('#pickup_tracking_number_container').fadeOut('fast');
					// $('.show_button').fadeIn('slow');
					$("#pickup_tracking_number").attr("required", false);
					$("#fedex_picup_options_container").fadeOut('fast');
					$("#usps_picup_options_container").fadeOut('fast');
					$("#fedex_picup_options").attr('required', false);
					$("#usps_picup_options").attr('required', false);
					$('.usps-specific').hide();
					$('.non-usps-specific').show();
				}else if(pickup_from == 'FedEx'){
					$("#fedex_picup_options_container").fadeIn('fast');
					$("#fedex_picup_options").attr('required', true);
					$("#next_to_tracking_field").attr("class", "col-lg-6");
					$('#pickup_tracking_number_container').fadeIn('slow');
					$("#usps_picup_options_container").fadeOut('fast');
					$("#usps_picup_options").attr('required', false);
					$('.usps-specific').hide();
					$('.non-usps-specific').show();
					// $('.show_button').fadeOut("fast");
				}else if(pickup_from == 'usps'){
					$("#usps_picup_options_container").fadeIn('fast');
					$("#usps_picup_options").attr('required', true);
					$('#pickup_tracking_number_container').fadeOut('fast');
					$("#pickup_tracking_number").attr("required", false);
					$("#next_to_tracking_field").attr("class", "col-lg-12");
					$("#fedex_picup_options").attr('required', false);
					$("#fedex_picup_options_container").fadeOut('fast');
					$('.usps-specific').show();
					$('.non-usps-specific').hide();
					$('#pickup_country_code').val("US").change();
				}else{
					$("#next_to_tracking_field").attr("class", "col-lg-6");
					$('#pickup_tracking_number_container').fadeIn('slow');
					// $('.show_button').fadeOut("fast");
					$("#pickup_tracking_number").attr("required", true);
					$("#fedex_picup_options_container").fadeOut('fast');
					$("#fedex_picup_options").attr('required', false);
					$("#usps_picup_options_container").fadeOut('fast');
					$("#usps_picup_options").attr('required', false);
					$('.usps-specific').hide();
					$('.non-usps-specific').show();
				}
			})

			$("#pickup_date").on("change", function(){
				if ($(this).val() == curDate) {
					initialize_slider();
				}else{
					initialize_slider(600);
				}
			})

			$(".msDropdown").msDropdown();

			$("#pickup_country_code").on("change", function(e){
				e.preventDefault();

				var pickup_zip_supported = $(this).find(':selected').data('pickup_zip_supported');
				if (pickup_zip_supported == 'Y') {
					$("#pickup_zip_code").html("Zip Code <span class='required'>*</span>");
				}else{
					$("#pickup_zip_code").html("Suburb <span class='required'>*</span>");
				}
				
				get_phone_coode($(this).val());

				if($("#pickup_from").val() == 'usps' && $('#pickup_country_code').val() != 'US'){
					$('#pickup_country_code').val("US").change();
				}
			})

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

			$("#pickup_tracking_number").on("blur", function(e){
				e.preventDefault();
				// var stringLength = $(this).val().length
				// if (stringLength >= 3) {
					$.ajax({
			           	type: "POST",
			           	url: '/get-ship-data',
			           	data: {'shipped_trackingNumber' : $(this).val()},
			           	dataType: 'JSON',
			           	success: function( response ) {
							if (response.status == 'error') {
								$(".result_not_found").html("<p>"+response.package_information+"</p>");
								$('.show_button').fadeOut("fast");
							}else{
								$(".result_not_found").html("");
								$('.show_button').fadeIn('slow');
								// var package_information = response.package_information;
								// var package_information_html = '<div class="row"><div class="col-lg-4"><label class="form-label">Flat rate items?</label><div class="custom-control custom-checkbox">';
								// $.each(JSON.parse(package_information), function(index,value){
								// 	var shipment_type_html = '';
								//     if (index == 'package_type') {
								//     	var checked = '';
								//     	if (value == 'contains_document') {
								//     		checked = 'checked';
								//     		shipment_type_html = '<div class="col-lg-12"><div class="form-group"><select class="form-control form-select disable_delivery_info required" required name="shipment_type" id="Shipment_type_dropdown"><option disabled selected value> -- Select Shipment Type -- </option><option value="contains_document">Document</option></select></div></div>';
								//     	}
								//     	package_information_html += '<input type="checkbox" name="flat_rate" class="custom-control-input disable_delivery_info" id="flatrate_checkbox" readonly '+checked+'></div></div>';
								    	
								//     }
								//     if (index == 'package_count') {
								//     	package_information_html += '<div class="col-lg-8"><div class="form-group"><label class="form-label">No. of Packages<span class="required">*</span></label><select class="form-control form-select disable_delivery_info required" name="package_count" id="package_count" readonly><option value="'+value+'">'+value+'</option></select></div></div>';
								//     }
								// 	package_information_html += '</div>';
								// 	if (shipment_type_html != "") {
								// 		package_information_html += '<div class="row" id="Shipment_type">'+shipment_type_html+'</div>';
								// 	}
								// 	package_information_html += '<div class="panel panel-default mb-4"><div class="panel-body">';
								// 	if (index == 'packages') {
								// 		var i;
								// 		package_information_html += '<ul id="education_fields" class="pkg-info-list">';
								// 		for (i = 0; i < value.length; ++i) {
								// 			var i_value = JSON.stringify(value[i]);
								// 		    package_information_html += '<li>';
								// 			$.each(JSON.parse(i_value), function(p_key,p_val){
								// 		    	if (p_key == 'weight') {
								// 					package_information_html += '<div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions[weight][]" value="'+p_val+'" readonly placeholder="Weight in lbs"></div>';
								// 				}
								// 				if (p_key == 'length') {
								// 					package_information_html += '<div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions[length][]" readonly value="'+p_val+'" placeholder="Length in inches"></div>';
								// 				}
								// 				if (p_key == 'width') {
								// 					package_information_html += '<div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions[width][]" readonly value="'+p_val+'" placeholder="Width in inches"></div>';
								// 				}
								// 				if (p_key == 'height') {
								// 					package_information_html += '<div class="form-group"><input type="text" class="form-control disable_delivery_info" required name="dimensions[height][]" readonly value="'+p_val+'" placeholder="Height in inches"></div>';
								// 				}
								// 			});
								// 			package_information_html += '</li>';
								// 		}
								// 	    package_information_html += '</ul>';
								// 	}
								// 	package_information_html += '</div></div>';
								// 	if (index == 'total_value') {
								// 		package_information_html += '<div class="form-group"><label class="form-label">Total Value <span class="required">*</span></label><input type="text" class="form-control disable_delivery_info" required name="total_value" placeholder="100" readonly value="'+value+'"></div>';
								// 	}
								// });
								// $("#package_information").html(package_information_html);
							}
						}
			       });
				// }
			})

			$("form#schedule_pickup").on("submit", function(e){
				$('html, body').animate({
			        scrollTop: $("#loader_image").offset().top
			    }, 2000);
				e.preventDefault();
				$.ajax({
		           	type: "POST",
		           	url: "{{url('/agent-schedule-pickup')}}",
		           	data: $(this).serialize(),
		           	dataType: 'JSON',
		           	beforeSend: function() {
		              	$("#loading-image").attr('src', "{{ asset('themes/' . $theme->folder . '/images/loaders/searching.gif') }}");
		           		$("#loading-image").show();
		           	},
					success: function( response ) {
						$("#loading-image").attr('src', "{{ asset('themes/' . $theme->folder . '/images/loaders/confirmBox.gif') }}");
		              	setTimeout(function () {
	                    	if (response.status == 'success') {
								$("#schedule_pickup").html("");
							}else{
								if (pickup_from != 'ups') {
									$('.show_button').fadeOut("fast");
								}
							}
							$(".delivery_information").html('<div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body" id="package_information"><div class="row"><p>'+response.message+'</p></div></div></div></div>');
			               	$("#loading-image").hide();
	                    }, 3000);
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

        $(document).on("change", "#Shipment_type_dropdown", function(e){
        	e.preventDefault();
        	$("#package_count").trigger("change");
		})

		min_Date = 0;
		if (new_day) {
			min_Date = 1;
		}

		var holiDays = <?php echo json_encode($shipping_page_info['us_holidays']);?>;  
		function disableHoliday(date) {
		    var string = $.datepicker.formatDate('yy-mm-dd', date);
		    var filterDate = new Date(string);
		    var day = filterDate.getDay();
		    var isHoliday = ($.inArray(string, holiDays) != -1);
		    return [day != 0 && day !=6 && !isHoliday]
		}
		jQuery($ => {
		  	// let isUnavailable = date => $.inArray(`${date.getDate()}-${(date.getMonth() + 1)}-${date.getFullYear()}`, unavailableDates) >= 0;
		  	$("#pickup_date").datepicker({
				dateFormat: "yy-mm-dd",
				changeMonth: false,
				changeYear: false,
				minDate: min_Date,
				// maxDate: '+60D',
				beforeShowDay: disableHoliday
		  	});
		});

</script>
@endsection