@extends('theme::layouts.app')

@section('content')


    <div class="sm:mx-auto sm:w-full sm:max-w-md sm:pt-10">
        <h2 class="text-3xl font-extrabold leading-9 text-center text-gray-900 sm:mt-6 lg:text-5xl">
            Sign up Below
        </h2>
        <p class="mt-4 text-sm leading-5 text-center text-gray-600 max-w">
            or, you can
            <a href="{{ route('login') }}" class="font-medium transition duration-150 ease-in-out text-wave-600 hover:text-wave-500 focus:outline-none focus:underline">
                login here
            </a>
        </p>
    </div>
    <main class="Shipping-layout">
        <div class="container max-w-7xl">
            <form role="form" method="POST" action="{{ route('register') }}">
            @csrf
            <!-- If we want the user to purchase before they can create an account -->
            <div class="pb-3 sm:border-b sm:border-gray-200">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    Profile
                </h3>
                <p class="max-w-2xl mt-1 text-sm leading-5 text-gray-500">
                    Information about your account.
                </p>
            </div>

            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-card">
                        <div class="form-head">
                            <h3>Shipper Information:</h3>
                        </div>
                        <div class="form-body">
                            <div class="row">
                            <input id="subs_id" type="hidden" name="subs_id" required class="w-full form-input" value="{{$subscriptions_id }}">
                            <input id="plan_id" type="hidden" name="plan_id" required class="w-full form-input" value="{{$plan_id }}">
                            <input id="customer_id" type="hidden" name="customer_id" required class="w-full form-input" value="{{$customer_id }}">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="name" class="block text-sm font-medium leading-5 text-gray-700">
                                            Name
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="name" type="text" name="name" required class="w-full form-input" value="{{ $customer->name }}" @if(!setting('billing.card_upfront')){{ 'autofocus' }}@endif>
                                        </div>
                                        @if ($errors->has('name'))
                                            <div class="mt-1 text-red-500">
                                                {{ $errors->first('name') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="email" class="block text-sm font-medium leading-5 text-gray-700">
                                            Email Address
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="email" type="email" name="email" value="{{ $customer->email; }}" required class="w-full form-input">
                                        </div>
                                        @if ($errors->has('email'))
                                            <div class="mt-1 text-red-500">
                                                {{ $errors->first('email') }}
                                            </div>
                                        @endif
                                    </div>    
                                </div>
                            </div> 
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-label">Country <span class="required">*</span></label>
                                        <input type="hidden" name="shipper_country_name" id="shipper_country_name" class="form-control disable_delivery_info" value="{{(isset($quote_request['shipper_country_name']) && !empty($quote_request['shipper_country_name']))?$quote_request['shipper_country_name']:''}}">
                                        <select class="form-control form-select disable_delivery_info msDropdown" required="" name="shipper_country" id="shipper_country" onChange="getCountry(this.value, 'shipper')">
                                            <option disabled="" selected="" value="">Select country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{$country->alpha_2_code}}" data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}">{{$country->country_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="form-group">
                                        <label class="form-label">Address <span class="required">*</span></label>                        
                                        <input type="text" class="form-control disable_delivery_info" required="" name="shipper_address" id="shipper_address" placeholder="Please enter your address">   
                                    </div>
                                </div>
                            </div>    
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">Zip Code <span class="required">*</span></label>                       
                                        <input type="text" class="form-control disable_delivery_info" required="" name="shipper_zip" id="shipper_zip" value="">   
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">City <span class="required">*</span></label>                        
                                        <input type="text" class="form-control disable_delivery_info" required="" name="shipper_city" id="shipper_city" value="">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">State <span class="required">*</span></label>                       
                                        <input type="text" class="form-control disable_delivery_info" required="" name="shipper_state" id="shipper_state" value="">  
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    @if(setting('auth.username_in_registration') && setting('auth.username_in_registration') == 'yes')
                                        <div class="col-lg-6">
                                            <label for="username" class="block text-sm font-medium leading-5 text-gray-700">
                                                Username
                                            </label>
                                            <div class="mt-1 rounded-md shadow-sm">
                                                <input id="username" type="text" name="username" value="{{ old('username') }}" required class="w-full form-input">
                                            </div>
                                            @if ($errors->has('username'))
                                                <div class="mt-1 text-red-500">
                                                    {{ $errors->first('username') }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif 
                                </div>   
                                <div class="col-lg-6">
                                    <div class="form-group shipper_phone">
                                        <label for="name" class="block text-sm font-medium leading-5 text-gray-700">
                                            Phone
                                        </label>
                                        <input id="shipper_phone_code" type="hidden" name="shipper_phone_code" required class="w-full form-input" value="">
                                        <input id="shipper_phone" type="number" name="shipper_phone" required class="w-full form-input" value="" @if(!setting('billing.card_upfront')){{ 'autofocus' }}@endif>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="password" class="block text-sm font-medium leading-5 text-gray-700">
                                            Password
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="password" type="password" name="password" required class="w-full form-input">
                                        </div>    
                                        @if ($errors->has('password'))
                                            <div class="mt-1 text-red-500">
                                                {{ $errors->first('password') }}
                                            </div>    
                                        @endif 
                                    </div>   
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="password_confirmation" class="block text-sm font-medium leading-5 text-gray-700">
                                            Confirm Password
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full form-input">  
                                        </div>    
                                        @if ($errors->has('password_confirmation'))
                                        <div class="mt-1 text-red-500">
                                            {{ $errors->first('password_confirmation') }}
                                        </div>    
                                        @endif
                                    </div>
                                </div>    
                            </div>   
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 receiver_info" id="receiver_information" style="display:none;">
                    <div class="form-card">
                        <div class="form-head">
                            <h3>Receiver Information:</h3>
                        </div>
                        <div class="form-body">
                            <div class="row">
                                <div class="mt-6 col-lg-6">
                                    <div class="form-group">
                                        <label for="name" class="block text-sm font-medium leading-5 text-gray-700">
                                            Name
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm">
                                            <input id="consignee_name" type="text" name="consignee_name" class="w-full form-input" value="{{ old('name') }}" @if(!setting('billing.card_upfront')){{ 'autofocus' }}@endif>
                                        </div>
                                        @if ($errors->has('name'))
                                            <div class="mt-1 text-red-500">
                                                {{ $errors->first('name') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-6 col-lg-6">
                                    <div class="form-group">
                                        <label for="name" class="block text-sm font-medium leading-5 text-gray-700">
                                            Phone
                                        </label>
                                        <div class="mt-1 rounded-md shadow-sm consignee_phone">
                                        <input id="consignee_phone_code" type="hidden" name="consignee_phone_code" class="w-full form-input" value="">
                                            <input id="consignee_phone" type="tel" name="consignee_phone" class="w-full form-input" value="" @if(!setting('billing.card_upfront')){{ 'autofocus' }}@endif>
                                        </div>
                                        @if ($errors->has('name'))
                                            <div class="mt-1 text-red-500">
                                                {{ $errors->first('name') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-label">Country <span class="required">*</span></label>

                                        <select class="form-control form-select disable_delivery_info consignee_msDropdown" name="consignee_country" id="consignee_country" onChange="getCountry(this.value, 'consignee')">
                                            <option disabled selected value>Select country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{$country->alpha_2_code}}" data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}" {{(isset($quote_request['to_country']) && $quote_request['to_country'] == $country->alpha_2_code)?'selected':''}}>{{$country->country_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>   
                                </div>
                                <div class="col-lg-9">
                                    <div class="form-group">
                                        <label class="form-label">Address <span class="required">*</span></label>                        
                                        <input type="text" class="form-control disable_delivery_info" name="consignee_address" id="consignee_address" placeholder="Please enter your address">
                                    </div>    
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">Zip Code <span class="required">*</span></label>                       
                                        <input type="text" class="form-control disable_delivery_info" name="consignee_zip" id="consignee_zip" value="">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">City <span class="required">*</span></label>                        
                                        <input type="text" class="form-control disable_delivery_info" name="consignee_city" id="consignee_city" value="">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">State <span class="required">*</span></label>                       
                                        <input type="text" class="form-control disable_delivery_info" name="consignee_state" id="consignee_state" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-card">
                        <div class="form-head">
                            <h3>Extra Confirmation:</h3>
                        </div>
                        <div class="form-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label"> Is Receiver Information is diffrent?: <span class="required">*</span></label>
                                        <select class="form-control form-select disable_delivery_info" required="" name="isreceiver_diff" id="isreceiver_diff">
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label"> Can we contact you by WhatsApp?: <span class="required">*</span></label>

                                        <select class="form-control form-select disable_delivery_info" required="" name="whatsapp_prefer" id="whatsapp_prefer">
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                            </div>
                            <div class="btn-wrap text-center">
                                <button type="submit" class="cstm-btn disable_delivery_info">Register</button>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('login') }}" class="mt-3 font-medium transition duration-150 ease-in-out text-wave-600 hover:text-wave-500 focus:outline-none focus:underline">
                                    Already have an account? Login here
                                </a>
                            </div>
                        </div>
                    </div>
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
        $(document).ready(function(){
                $("#isreceiver_diff").change(function() {
                if ($(this).val() == "yes"){
                    $("#receiver_information").show();
                    $(".consignee_msDropdown").msDropdown();
                    $("#consignee_name").prop('required',true);
                    $("#consignee_phone").prop('required',true);
                    $("#consignee_country").prop('required',true);
                    $("#consignee_address").prop('required',true);
                    $("#consignee_zip").prop('required',true);
                    $("#consignee_city").prop('required',true);
                    $("#consignee_state").prop('required',true);
                    
                }else{
                    $("#receiver_information").hide();
                    $("#consignee_name").prop('required',false);
                    $("#consignee_phone").prop('required',false);
                    $("#consignee_country").prop('required',false);
                    $("#consignee_address").prop('required',false);
                    $("#consignee_zip").prop('required',false);
                    $("#consignee_city").prop('required',false);
                    $("#consignee_state").prop('required',false);
                }   
            }); 
        });    

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		    }
		});
		var room = <?=(isset($quote_request['package_count']) && !empty($quote_request['package_count']))?$quote_request['package_count']:1?>;
      	var componentForm = {
		  	//street_number: 'short_name',
		  	//route: 'long_name',
		  	shipper_city: 'long_name',
		  	shipper_state: 'short_name',
		  	shipper_zip: 'short_name'
		};
		var to_componentForm = {
		  	//street_number: 'short_name',
		  	//route: 'long_name',
		  	consignee_city: 'long_name',
		  	consignee_state: 'short_name',
		  	consignee_zip: 'short_name'
		};
		function getCountry(country, address_type='from') {
          	if(country != undefined){
				document.getElementById(address_type+'_address').value = '';
				document.getElementById(address_type+'_state').value = '';
				document.getElementById(address_type+'_city').value = '';
				document.getElementById(address_type+'_zip').value = '';
				initAutocomplete(country, address_type);
				if (address_type == 'shipper') {
                    get_shipper_phone_coode(country);
					$("#from_country_name").val($("#shipper_country_code option:selected").text());
				}else{
                    get_consignee_phone_coode(country);
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
		    		var addressType = 'consignee_state';
				}
				if(place.address_components[i].types[0] == 'locality') {
		    		var addressType = 'consignee_city';
				}
				if(place.address_components[i].types[0] == 'postal_code') {
		    		var addressType = 'consignee_zip';
				}
				if (to_componentForm[addressType]) {
		      		var val = place.address_components[i][to_componentForm[addressType]];
		      		document.getElementById(addressType).value = val;
		    	}
		  	}
		  	document.getElementById('consignee_address').value = place.address_components[0]['short_name'] + ' ' + place.address_components[1]['short_name'];
		}

        function get_shipper_phone_coode(country_code) {
			$(".shipper_phone .iti__flag-container").remove();
			var pickup_phone = document.querySelector("#shipper_phone");
		    window.intlTelInput(pickup_phone, {
		      	allowDropdown: false, 
			  	initialCountry: country_code,
				separateDialCode: true,
		      	utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
		    });
            var countryCode = $('.shipper_phone .iti__selected-flag').attr('title');
            var countryCode = countryCode.replace(/[^0-9\++]/g,'');
            $("#shipper_phone_code").val(countryCode);
		}
        function get_consignee_phone_coode(country_code) {
			$(".consignee_phone .iti__flag-container").remove();
			var pickup_phone = document.querySelector("#consignee_phone");
		    window.intlTelInput(pickup_phone, {
		      	allowDropdown: false, 
			  	initialCountry: country_code,
				separateDialCode: true,
		      	utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
		    });
            var countryCode = $('.consignee_phone .iti__selected-flag').attr('title');
            var countryCode = countryCode.replace(/[^0-9\++]/g,'');
            $("#consignee_phone_code").val(countryCode);
		}

		$(document).ready(function(){
			$(".msDropdown").msDropdown();
        });    
	</script>
@endsection
