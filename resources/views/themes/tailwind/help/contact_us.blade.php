@extends('theme::layouts.app')

@section('content')

<main class="Shipping-layout">
    <div class="container max-w-7xl">
        <form id="contact_form" role="form" method="POST" action="{{ route('wave.ContactUsSave') }}">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="form-card">
                    <div class="form-head">
						<h3>Contact Us</h3>
					</div>
                    <div class="form-body">
                        <div class="row">
                        
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        Name <span class="required">*</span>
                                    </label>
                                    <div class="mt-1 rounded-md shadow-sm">
                                        <input id="name" type="text" name="name" required class="form-control" value="" >
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
                                    <label for="email" class="form-label">
                                        Email Address <span class="required">*</span>
                                    </label>
                                    <div class="mt-1 rounded-md shadow-sm">
                                        <input id="email" type="email" name="email" value="" required class="form-control">
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
									<input type="hidden" name="from_country_name" id="from_country_name" class="form-control disable_delivery_info" value="{{(isset($quote_request['from_country_name']) && !empty($quote_request['from_country_name']))?$quote_request['from_country_name']:''}}">

									<select class="form-control form-select disable_delivery_info msDropdown" required name="country" id="from_country_code" onChange="getCountry(this.value, 'from')">
										<option disabled selected value> -- Select country -- </option>
										@foreach ($countries as $country)
											<option value="{{$country->alpha_2_code}}" data-from_zip_supported={{$country->zip_code_supported}} data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}" {{(isset($quote_request['from_country']) && $quote_request['from_country'] == $country->alpha_2_code)?'selected':''}} >{{$country->country_name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-lg-9">
								<div class="form-group">
									<label class="form-label">Address <span class="required">*</span></label>                                   
									<input type="text" class="form-control disable_delivery_info" required name="address" id="from_address" placeholder="Enter a location" value="{{(isset($quote_request['from_address']) && !empty($quote_request['from_address']))?$quote_request['from_address']:''}}">
								</div>
							</div>
                        </div>
                        <div class = "row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="form-label" id="from_zip_code">Zip Code <span class="required">*</span></label>                       
                                    <input type="text" class="form-control disable_delivery_info" required name="zip"  id="from_zip" value="{{(isset($quote_request['from_zip']) && !empty($quote_request['from_zip']))?$quote_request['from_zip']:''}}">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label class="form-label">City <span class="required">*</span></label>                        
                                    <input type="text" class="form-control disable_delivery_info" required name="city" id="from_city" value="{{(isset($quote_request['from_city']) && !empty($quote_request['from_city']))?$quote_request['from_city']:''}}">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="form-label">State <span class="required">*</span></label>                       
                                    <input type="text" class="form-control disable_delivery_info" required name="state" id="from_state" value="{{(isset($quote_request['from_state']) && !empty($quote_request['from_state']))?$quote_request['from_state']:''}}">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone <span class="required">*</span></label>
                                    <input id="phone" type="phone" name="phone" value="" required class="form-control">
                                    
                                    @if ($errors->has('phone'))
                                        <div class="mt-1 text-red-500">
                                            {{ $errors->first('phone') }}
                                        </div>
                                    @endif
                                </div>    
                            </div>
                        </div>
                        
                        <div class="row">
                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="message" class="form-label">
                                        Message <span class="required">*</span>
                                    </label>
                                    <div class="mt-1 rounded-md shadow-sm">
                                        <textarea id="message" type="text" name="message" required class="w-full form-control" value="" style="height: 150px;"></textarea>
                                    </div>
                                    @if ($errors->has('message'))
                                        <div class="mt-1 text-red-500">
                                            {{ $errors->first('message') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <div class="g-recaptcha" data-sitekey="{{env("recaptcha_site_key")}}"></div>
                                </div>
                            </div>
                            
                            
                        </div>
                        <div class="btn-wrap text-center">
                            <button type="submit" class="cstm-btn disable_delivery_info">Submit</button>
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
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBvqndqKM903bY5xn03L2F0kFrL5B7_3vk&libraries=places" async defer></script>
	<script>
		
		
      	var componentForm = {
		  	//street_number: 'short_name',
		  	//route: 'long_name',
		  	from_city: 'long_name',
		  	from_state: 'short_name',
		  	from_zip: 'short_name'
		};
		
		$( "#from_address" ).click(function() {
			var from_country = $("#from_country_code").children(":selected").val();
			if(from_country != ""){
				initAutocomplete(from_country,'from');
			}
		});
		
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
				if (componentForm[addressType]) {
		      		var val = place.address_components[i][componentForm[addressType]];
		      		document.getElementById(addressType).value = val;
		    	}
		  	}
		  	document.getElementById('from_address').value = place.address_components[0]['short_name'] + ' ' + place.address_components[1]['short_name'];
		}

		$(document).ready(function(){

			$("#from_country_code").on("change", function(){
				var from_zip_supported = $(this).find(':selected').data('from_zip_supported');
				if (from_zip_supported == 'Y') {
					$("#from_zip_code").html("Zip Code <span class='required'>*</span>");
				}else{
					$("#from_zip_code").html("Suburb <span class='required'>*</span>");
				}
				var from_country_code 	= $("#from_country_code option:selected").val();
                get_phone_coode($(this).val());
			})

			

			$(".msDropdown").msDropdown();

            $('#contact_form').submit(function(){
                var response = grecaptcha.getResponse(0);
                if(response.length == 0)
                {
                    //reCaptcha not verified
                    alert("Please verify you are human!");
                    return false;
                }
                
            });
		})

		function get_phone_coode(country_code) {
			$(".iti__flag-container").remove();
			var pickup_phone = document.querySelector("#phone");
		    window.intlTelInput(pickup_phone, {
		      	allowDropdown: false, 
			  	initialCountry: country_code,
				separateDialCode: true,
		      	utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
		    });
		}
		
	</script>
@endsection