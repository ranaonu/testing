@extends('theme::layouts.app')

@section('content')
<main class="Shipping-layout">
	<div class="container max-w-7xl">
		<form class="get_quote_form" id="get_quote_form">
		<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
		<input type="hidden" name="delivery_location" id="delivery_location" value="">
		<div class="row">
			<div class="col-lg-12">
				<div class="form-card">
					<div class="form-head">
						<h3>From:</h3>
					</div>
					<div class="form-body">
						<div class="row">
							<div class="col-lg-3">
								<div class="form-group">
									<label class="form-label">Country <span class="required">*</span></label>
									<input type="hidden" name="from_country_name" id="from_country_name" class="form-control disable_delivery_info" value="{{(isset($quote_request['from_country_name']) && !empty($quote_request['from_country_name']))?$quote_request['from_country_name']:''}}">

									<select class="form-control form-select disable_delivery_info msDropdown" required name="from_country" id="from_country_code" onChange="getCountry(this.value, 'from')">
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
									<input type="text" class="form-control disable_delivery_info" required name="from_address" id="from_address" placeholder="Enter a location" value="{{(isset($quote_request['from_address']) && !empty($quote_request['from_address']))?$quote_request['from_address']:''}}">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label class="form-label" id="from_zip_code">Zip Code <span class="required">*</span></label>                       
									<input type="text" class="form-control disable_delivery_info" required name="from_zip"  id="from_zip" value="{{(isset($quote_request['from_zip']) && !empty($quote_request['from_zip']))?$quote_request['from_zip']:''}}">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label class="form-label">City <span class="required">*</span></label>                        
									<input type="text" class="form-control disable_delivery_info" required name="from_city" id="from_city" value="{{(isset($quote_request['from_city']) && !empty($quote_request['from_city']))?$quote_request['from_city']:''}}">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label class="form-label">State <span class="required">*</span></label>                       
									<input type="text" class="form-control disable_delivery_info" required name="from_state" id="from_state" value="{{(isset($quote_request['from_state']) && !empty($quote_request['from_state']))?$quote_request['from_state']:''}}">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			@if(!auth()->guest())
			<div class="col-lg-12" id="saved_consignee_details">
				<div class="form-card">
					<div class="form-head">
						<h3>To:</h3>
					</div>
					<input type="hidden" id="consignees_id" name="consignees_id" value="">
					<div class="form-body">	
						<div class="row">
							<div class="col-lg-6">
								<label class="form-label">New Consinee?</label>
								<div class="custom-control custom-checkbox">
									<input type="radio" name="consinee_type" class="custom-control-input disable_delivery_info" id="consinee_type" value="new" checked="checked" value="existing">
								</div>
							</div>
							<div class="col-lg-6">
								<label class="form-label">Existing Consinee?</label>
								<div class="custom-control custom-checkbox">
									<input type="radio" name="consinee_type" class="custom-control-input disable_delivery_info" id="consinee_type" value="existing">
								</div>
							</div>
						</div>
						<div class="row pt-5" id="existing_consinees" style="display:none;">
							<div class="col-lg-12">
								<div class="form-group">
									<label class="form-label">Consignee <span class="required">*</span></label>
									<select class="form-control form-select" name="consignee_id" id="consignee_id">
										<option disabled selected value> -- Select Consignee -- </option>
										@foreach ($consignees as $consignee)
										<option value="{{$consignee->id}}" data-name="{{$consignee->consignee_name}}" data-country="{{$consignee->consignee_address_country}}" data-address="{{$consignee->consignee_address}}" data-zip="{{$consignee->consignee_address_zip}}" data-city="{{$consignee->consignee_address_city}}" data-state="{{$consignee->consignee_address_state}}" data-phone="{{$consignee->consignee_phone}}" data-phone2="{{$consignee->consignee_homephone}}">{{$consignee->consignee_name}}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
						<div class="pt-5" id="new_consinee_details">
							<div class="row">
								<div class="col-lg-12 new_consignee_hide" style="display:none;">
									<div class="form-group">
										<label class="form-label">Consignee Name <span class="required">*</span></label>                                   
										<input type="text" class="form-control disable_delivery_info required" name="consignee_name" id="consignee_name" placeholder="Please enter Consignee name" value="">
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label class="form-label">Country <span class="required">*</span></label>
										<input type="hidden" name="to_country_name" id="to_country_name" class="form-control disable_delivery_info" value="{{(isset($quote_request['to_country_name']) && !empty($quote_request['to_country_name']))?$quote_request['to_country_name']:''}}">
										<select class="form-control form-select disable_delivery_info msDropdown" required name="to_country" id="to_country_code" onChange="getCountry(this.value, 'to')">
											<option disabled selected value> -- Select country -- </option>
											@foreach ($countries as $country)
												<option value="{{$country->alpha_2_code}}" data-to_zip_supported={{$country->zip_code_supported}} data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}" {{(isset($quote_request['to_country']) && $quote_request['to_country'] == $country->alpha_2_code)?'selected':''}}>{{$country->country_name}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-lg-9">
									<div class="form-group">
										<label class="form-label">Address <span class="required">*</span></label>                                   
										<input type="text" class="form-control disable_delivery_info required" name="to_address" id="to_address" placeholder="Enter a location" value="{{(isset($quote_request['to_address']) && !empty($quote_request['to_address']))?$quote_request['to_address']:''}}">
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-label" id="to_zip_code">Zip Code <!-- <span class="required">*</span> --></label>                       
										<input type="text" class="form-control disable_delivery_info required" name="to_zip" id="to_zip" value="{{(isset($quote_request['to_zip']) && !empty($quote_request['to_zip']))?$quote_request['to_zip']:''}}">
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-label">City <span class="required">*</span></label>                        
										<input type="text" class="form-control disable_delivery_info required" name="to_city" id="to_city" value="{{(isset($quote_request['to_city']) && !empty($quote_request['to_city']))?$quote_request['to_city']:''}}">
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-label">State <span class="required">*</span></label>                       
										<input type="text" class="form-control disable_delivery_info required" name="to_state" id="to_state"  value="{{(isset($quote_request['to_state']) && !empty($quote_request['to_state']))?$quote_request['to_state']:''}}">
									</div>
								</div>
								<div class="new_consignee_hide" style="display:none;">
									<div class="col-lg-6">
										<div class="form-group">
											<label class="form-label" id="to_zip_code">Consignee Phone number <!-- <span class="required">*</span> --></label>                       
											<input type="tel" class="form-control disable_delivery_info required" name="consignee_phone" id="consignee_phone" placeholder="Please enter the Consignee Phone" value="">
										</div>
									</div>
									<div class="col-lg-6">
										<div class="form-group">
											<label class="form-label" id="to_zip_code">Consignee home number <!-- <span class="required">*</span> --></label>                       
											<input type="tel" class="form-control disable_delivery_info required" name="consignee_homephone" id="consignee_homephone" placeholder="Please enter the Consignee Phone 2" value="">
										</div>
									</div>
								</div>
							</div>	
						</div>			
					</div>	
				</div>		
			</div>	
			@else
			<div class="col-lg-12" id="new_consignee_details">
				<div class="form-card">
					<div class="form-head">
						<h3>To:</h3>
					</div>
					<div class="form-body">
						<div class="row">
							<div class="col-lg-3">
								<div class="form-group">
									<label class="form-label">Country <span class="required">*</span></label>
									<input type="hidden" name="to_country_name" id="to_country_name" class="form-control disable_delivery_info" value="{{(isset($quote_request['to_country_name']) && !empty($quote_request['to_country_name']))?$quote_request['to_country_name']:''}}">
									<select class="form-control form-select disable_delivery_info" required name="to_country" id="to_country_code" onChange="getCountry(this.value, 'to')">
										<option disabled selected value> -- Select country -- </option>
										@foreach ($countries as $country)
											<option value="{{$country->alpha_2_code}}" data-to_zip_supported={{$country->zip_code_supported}} data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}" {{(isset($quote_request['to_country']) && $quote_request['to_country'] == $country->alpha_2_code)?'selected':''}}>{{$country->country_name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-lg-9">
								<div class="form-group">
									<label class="form-label">Address <span class="required">*</span></label>                                   
									<input type="text" class="form-control disable_delivery_info required" name="to_address" id="to_address" placeholder="Enter a location" value="{{(isset($quote_request['to_address']) && !empty($quote_request['to_address']))?$quote_request['to_address']:''}}">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label class="form-label" id="to_zip_code">Zip Code <!-- <span class="required">*</span> --></label>                       
									<input type="text" class="form-control disable_delivery_info required" name="to_zip" id="to_zip" value="{{(isset($quote_request['to_zip']) && !empty($quote_request['to_zip']))?$quote_request['to_zip']:''}}">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label class="form-label">City <span class="required">*</span></label>                        
									<input type="text" class="form-control disable_delivery_info required" name="to_city" id="to_city" value="{{(isset($quote_request['to_city']) && !empty($quote_request['to_city']))?$quote_request['to_city']:''}}">
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label class="form-label">State <span class="required">*</span></label>                       
									<input type="text" class="form-control disable_delivery_info required" name="to_state" id="to_state"  value="{{(isset($quote_request['to_state']) && !empty($quote_request['to_state']))?$quote_request['to_state']:''}}">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			@endif
			<div class="col-lg-12">
				<div class="form-card">
					<div class="form-head">
						<h3>Package Information</h3>
					</div>
					<div class="form-body">
						<div class="row">
							<div class="col-lg-4">
								<label class="form-label">Flat rate items?</label>
								<div class="custom-control custom-checkbox">
									<input type="checkbox" name="flat_rate" class="custom-control-input disable_delivery_info" id="flatrate_checkbox" {{(isset($quote_request['flat_rate']) && $quote_request['flat_rate'] == 'on')?'checked':''}}>
								</div>
							</div>
							<div class="col-lg-8">
								<div class="form-group">
									<label class="form-label">No. of Packages<span class="required">*</span></label>
									<select class="form-control form-select disable_delivery_info required" name="package_count" id="package_count">
										@for ($i = 1; $i <= 25; $i++)
									        <option value="{{ $i }}" {{(isset($quote_request['package_count']) && $quote_request['package_count'] == $i)?'selected':''}}>{{ $i }}</option>
									    @endfor
									</select>
								</div>
							</div>
						</div>
						<div class="row" id="Shipment_type">
							@if(isset($quote_request['shipment_type']) && $quote_request['shipment_type'] == 'contains_document')
								<div class="col-lg-12">
									<div class="form-group">
										<select class="form-control form-select disable_delivery_info required" required name="shipment_type" id="Shipment_type_dropdown">
											<option disabled value> -- Select Shipment Type -- </option>
											<option selected value="contains_document">Document</option>
										</select>
									</div>
								</div>
							@endif
						</div>
						<div class="panel panel-default mb-4">
							<div class="panel-body">
								<ul id="education_fields" class="pkg-info-list">
									@if(isset($quote_request['packages']))
										@foreach($quote_request['packages'] as $package_num => $package)
											@if($package_num == 0)
												<li>
											@else
												<li class="{{'removeclass'.($package_num+1)}}">
											@endif
											
												<div class="form-group">
													<input type="text" class="form-control disable_delivery_info" id="package_weight" required name="dimensions[weight][]" placeholder="Weight in lbs" value="{{$package['weight']}}" {{(isset($quote_request['shipment_type']) && $quote_request['shipment_type'] == 'contains_document')?'readonly':''}}>
													<div class="alert alert-danger weight_error result_not_found" style="display: none;"><p>Weight can not be more than 150 lbs</p></div>
												</div>
												<div class="form-group">
													<input type="text" class="form-control disable_delivery_info dimensions" id="package_length" required name="dimensions[length][]" placeholder="Length in inches" value="{{$package['length']}}" {{(isset($quote_request['shipment_type']) && $quote_request['shipment_type'] == 'contains_document')?'readonly':''}}>
													<div class="alert alert-danger length_error result_not_found" style="display: none;"><p>Length can not be more than 80 in</p></div>
												</div>
												<div class="form-group">
													<input type="text" class="form-control disable_delivery_info dimensions" id="package_width" required name="dimensions[width][]" placeholder="Width in inches" value="{{$package['width']}}" {{(isset($quote_request['shipment_type']) && $quote_request['shipment_type'] == 'contains_document')?'readonly':''}}>
													<div class="alert alert-danger width_error result_not_found" style="display: none;"><p>Width can not be more than 80 in</p></div>
												</div>
												<div class="form-group">
													<input type="text" class="form-control disable_delivery_info dimensions" id="package_height" required name="dimensions[height][]" placeholder="Height in inches" value="{{$package['height']}}" {{(isset($quote_request['shipment_type']) && $quote_request['shipment_type'] == 'contains_document')?'readonly':''}}>
													<div class="alert alert-danger height_error result_not_found" style="display: none;"><p>Height can not be more than 80 in</p></div>
												</div>
												@if($package_num == 0)
													<button class="btn btn-success disable_delivery_info" type="button" onclick="education_fields();"> 
													<i class="fas fa-plus"></i> </button>
												@else
													<button class="btn btn-danger" type="button" onclick="remove_education_fields({{($package_num+1)}});"> <i class="fas fa-trash-alt"></i> </button>
												@endif
											</li>
										@endforeach
									@else
										<li>
											<div class="form-group">
												<input type="text" class="form-control disable_delivery_info" id="package_weight" required name="dimensions[weight][]" placeholder="Weight in lbs">
												<div class="alert alert-danger weight_error result_not_found" style="display: none;"><p>Weight can not be more than 150 lbs</p></div>
											</div>
											<div class="form-group">
												<input type="text" class="form-control disable_delivery_info dimensions" id="package_length" required name="dimensions[length][]" placeholder="Length in inches">
												<div class="alert alert-danger length_error result_not_found" style="display: none;"><p>Length can not be more than 80 in</p></div>
											</div>
											<div class="form-group">
												<input type="text" class="form-control disable_delivery_info dimensions" id="package_width" required name="dimensions[width][]" placeholder="Width in inches">
												<div class="alert alert-danger width_error result_not_found" style="display: none;"><p>Width can not be more than 80 in</p></div>
											</div>
											<div class="form-group">
												<input type="text" class="form-control disable_delivery_info dimensions" id="package_height" required name="dimensions[height][]" placeholder="Height in inches">
												<div class="alert alert-danger height_error result_not_found" style="display: none;"><p>Height can not be more than 80 in</p></div>
											</div>
											<button class="btn btn-success disable_delivery_info" type="button" onclick="education_fields();"> 
											<i class="fas fa-plus"></i> </button>
										</li>
									@endif
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label class="form-label">Total Value <span class="required total_astrick">*</span></label>
							<input type="text" class="form-control disable_delivery_info" required id="total_value" name="total_value" placeholder="Enter a Total Shipment value" value="{{(isset($quote_request['total_value']) && !empty($quote_request['total_value']))?$quote_request['total_value']:''}}">
						</div>
						<div class="btn-wrap text-center">
							<button type="submit" class="cstm-btn disable_delivery_info">Get a Quotes</button>
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
<style>
#saved_consignee_details #consinee_type , #flatrate_checkbox{
	width:15px;
	height:15px;
}
.iti--separate-dial-code input[type=tel]{
	padding-left:66px !important;
}
</style>
@section('javascript')
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBvqndqKM903bY5xn03L2F0kFrL5B7_3vk&libraries=places" async defer></script>
	<script>
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
		$( "#from_address" ).click(function() {
			var from_country = $("#from_country_code").children(":selected").val();
			if(from_country != ""){
				initAutocomplete(from_country,'from');
			}
		});
		$( "#to_address" ).click(function() {
			var to_country = $("#to_country_code").children(":selected").val();
			if(to_country != ""){
				initAutocomplete(to_country,'to');
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
				if (to_componentForm[addressType]) {
		      		var val = place.address_components[i][to_componentForm[addressType]];
		      		document.getElementById(addressType).value = val;
		    	}
		  	}
		  	document.getElementById('to_address').value = place.address_components[0]['short_name'] + ' ' + place.address_components[1]['short_name'];
		}

		function value_required_update(from_country, to_country){
			if (from_country == to_country) {
				$("#total_value").prop("required", false);
				$(".total_astrick").html("");
			}else{
				$("#total_value").prop("required", true);
				$(".total_astrick").html("*");
			}
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
				var to_country_code 	= $("#to_country_code option:selected").val();
				value_required_update(from_country_code, to_country_code);
			})

			$("#to_country_code").on("change", function(){
				var to_zip_supported = $(this).find(':selected').data('to_zip_supported');
				if (to_zip_supported == 'Y') {
					$("#to_zip_code").text("Zip Code");
				}else{
					$("#to_zip_code").text("Suburb");
				}

				var from_country_code 	= $("#from_country_code option:selected").val();
				var to_country_code 	= $("#to_country_code option:selected").val();
				value_required_update(from_country_code, to_country_code);
				
			})

			$(".msDropdown").msDropdown();

			$("#flatrate_checkbox").on("change", function(){
				$("#package_count").trigger("change");
				if(this.checked) {
			        $("#Shipment_type").html('<div class="col-lg-12"><div class="form-group"><select class="form-control form-select disable_delivery_info required" required name="shipment_type" id="Shipment_type_dropdown"><option disabled selected value> -- Select Shipment Type -- </option><option value="contains_document">DOCUMENT</option><optgroup label=" -- PHONE -- "><option value="phone_new">PHONE NEW</option><option value="phone_used">PHONE USED</option></optgroup><optgroup label=" -- TABLET -- "><option value="tablet_new">TABLET NEW</option><option value="tablet_used">TABLET USED</option></optgroup><optgroup label=" -- LAPTOP -- "><option value="laptop_new">LAPTOP NEW</option><option value="laptop_used">LAPTOP USED</option></optgroup><optgroup label=" -- TV -- "><option value="tv_24">TV 24"</option><option value="tv_32">TV 32"</option><option value="tv_40">TV 40"</option><option value="tv_42">TV 42"</option><option value="tv_50">TV 50"</option><option value="tv_55">TV 55"</option><option value="tv_60">TV 60"</option><option value="tv_65">TV 65"</option><option value="tv_70">TV 70"</option><option value="tv_75">TV 75"</option><option value="tv_80">TV 80"</option><option value="tv_85">TV 85"</option></optgroup><optgroup label=" -- BARREL -- "><option value="barrel_55_gal">BARREL 55 GAL</option></optgroup><option value="luggage_checked">LUGGAGE (CHECKED BAG)</option><option value="econtainer">E-CONTAINER</option><optgroup label=" -- HUB -- "><option value="hub_14_14_14">HUB20 14X14X14</option><option value="hub_18_18_16">HUB20 18X18X16</option><option value="hub_18_18_24">HUBB20 18X18X24</option><option value="hub_18_24_24">HUBB20 18X24X24</option></optgroup></select></div></div>');
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
				if ($('#flatrate_checkbox').is(":checked") && ($("#Shipment_type_dropdown").val() == 'phone_new' || $("#Shipment_type_dropdown").val() == 'phone_used')) {
					weight = 2;
					length = 9;
					width  = 6;
					height = 2;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && ($("#Shipment_type_dropdown").val() == 'tablet_new' || $("#Shipment_type_dropdown").val() == 'tablet_used')) {
					weight = 2;
					length = 12;
					width  = 9;
					height = 3;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && ($("#Shipment_type_dropdown").val() == 'laptop_new' || $("#Shipment_type_dropdown").val() == 'laptop_used')) {
					weight = 5;
					length = 18;
					width  = 12;
					height = 4;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_24') {
					weight = 12;
					length = 22;
					width  = 15;
					height = 4;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_32') {
					weight = 14;
					length = 29;
					width  = 19;
					height = 4;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_40') {
					weight = 16;
					length = 37;
					width  = 22;
					height = 6;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_42') {
					weight = 20;
					length = 38;
					width  = 24;
					height = 6;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_50') {
					weight = 25;
					length = 44;
					width  = 27;
					height = 7;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_55') {
					weight = 35;
					length = 49;
					width  = 30;
					height = 7;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_60') {
					weight = 40;
					length = 54;
					width  = 33;
					height = 8;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_65') {
					weight = 50;
					length = 58;
					width  = 35;
					height = 8;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_70') {
					weight = 60;
					length = 62;
					width  = 37;
					height = 9;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_75') {
					weight = 70;
					length = 66;
					width  = 40;
					height = 9;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_80') {
					weight = 80;
					length = 70;
					width  = 45;
					height = 10;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'tv_85') {
					weight = 95;
					length = 75;
					width  = 45;
					height = 10;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'barrel_55_gal') {
					weight = 200;
					length = 34;
					width  = 24;
					height = 24;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'barrel_55_gal') {
					weight = 200;
					length = 34;
					width  = 24;
					height = 24;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'luggage_checked') {
					weight = 50;
					length = 30;
					width  = 20;
					height = 12;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'econtainer') {
					weight = 250;
					length = 42;
					width  = 29;
					height = 25;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'hub_14_14_14') {
					weight = 20;
					length = 14;
					width  = 14;
					height = 14;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'hub_18_18_16') {
					weight = 40;
					length = 18;
					width  = 18;
					height = 16;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'hub_18_18_24') {
					weight = 60;
					length = 18;
					width  = 18;
					height = 24;
					readonly = 'readonly';
				}
				if ($('#flatrate_checkbox').is(":checked") && $("#Shipment_type_dropdown").val() == 'hub_18_24_24') {
					weight = 80;
					length = 18;
					width  = 24;
					height = 24;
					readonly = 'readonly';
				}
				var objTo = document.getElementById('education_fields');
				var divtest = '';
				room = $(this).val();

				for (var i = 1; i <= room; i++) {
					divtest += '<li class="removeclass'+i+'"><div class="form-group"><input type="text" class="form-control disable_delivery_info" id="package_weight" required name="dimensions['+"weight"+'][]" placeholder="Weight in lbs" value="'+weight+'" '+readonly+'><div class="alert alert-danger weight_error result_not_found" style="display: none;"><p>Weight can not be more than 150 lbs</p></div></div><div class="form-group"><input type="text" class="form-control disable_delivery_info dimensions" id="package_length" required name="dimensions['+"length"+'][]" placeholder="Length" value="'+length+'" '+readonly+'><div class="alert alert-danger length_error result_not_found" style="display: none;"><p>Length can not be more than 80 in</p></div></div><div class="form-group"><input type="text" class="form-control disable_delivery_info dimensions" id="package_width" required name="dimensions['+"width"+'][]" placeholder="Width" value="'+width+'" '+readonly+'><div class="alert alert-danger width_error result_not_found" style="display: none;"><p>Width can not be more than 80 in</p></div></div><div class="form-group"><input type="text" class="form-control disable_delivery_info dimensions" id="package_height" required name="dimensions['+"height"+'][]" placeholder="Height" value="'+height+'" '+readonly+'><div class="alert alert-danger height_error result_not_found" style="display: none;"><p>Height can not be more than 80 in</p></div></div>';

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
				$('html, body').animate({
			        scrollTop: $("#loader_image").offset().top
			    }, 2000);
				e.preventDefault();

				var consignee_type = $('input[type=radio][name=consinee_type]:checked').val();
				if(consignee_type == "new"){
					$.ajax({
						type: "POST",
						url: '/save-consignee',
						data: $(this).serialize(),
						dataType: 'JSON',
						success: function( response ) {
							$("#consignees_id").val(response.consignee_id);
							$.ajax({
								type: "POST",
								url: '/get-quote-result',
								data: $("form#get_quote_form").serialize(),
								dataType: 'JSON',
								beforeSend: function() {
									$("#loading-image").attr('src', "{{ asset('themes/' . $theme->folder . '/images/loaders/searching.gif') }}");
									$("#loading-image").show();
								},
								success: function( response ) {
									$("#loading-image").attr('src', "{{ asset('themes/' . $theme->folder . '/images/loaders/confirmBox.gif') }}");
									setTimeout(function () {
										$("#loading-image").hide();
										$(".delivery_information").html(response.html);
									}, 3000);
								}
							});	
						}
					});
				}else{
					$.ajax({
						type: "POST",
						url: '/get-quote-result',
						data: $(this).serialize(),
						dataType: 'JSON',
						beforeSend: function() {
							$("#loading-image").attr('src', "{{ asset('themes/' . $theme->folder . '/images/loaders/searching.gif') }}");
							$("#loading-image").show();
						},
						success: function( response ) {
							$("#loading-image").attr('src', "{{ asset('themes/' . $theme->folder . '/images/loaders/confirmBox.gif') }}");
							setTimeout(function () {
								$("#loading-image").hide();
								$(".delivery_information").html(response.html);
							}, 3000);
						}
					});
				}
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
		    divtest.innerHTML = '<div class="form-group"><input type="text" class="form-control disable_delivery_info" required id="package_weight" name="dimensions['+"weight"+'][]" placeholder="Weight in lbs" value="'+weight+'" '+readonly+'><div class="alert alert-danger weight_error result_not_found" style="display: none;"><p>Weight can not be more than 150 lbs</p></div></div><div class="form-group"><input type="text" class="form-control disable_delivery_info dimensions" id="package_length" required name="dimensions['+"length"+'][]" placeholder="Length" value="'+length+'" '+readonly+'><div class="alert alert-danger length_error result_not_found" style="display: none;"><p>Length can not be more than 80 in</p></div></div><div class="form-group"><input type="text" class="form-control disable_delivery_info dimensions" id="package_width" required name="dimensions['+"width"+'][]"  value="'+width+'" '+readonly+' placeholder="Width"><div class="alert alert-danger width_error result_not_found" style="display: none;"><p>Width can not be more than 80 in</p></div></div><div class="form-group"><input type="text" class="form-control disable_delivery_info dimensions" id="package_height" required name="dimensions['+"height"+'][]" placeholder="Height" value="'+height+'" '+readonly+'><div class="alert alert-danger height_error result_not_found" style="display: none;"><p>Height can not be more than 80 in</p></div></div><button class="btn btn-danger" type="button" onclick="remove_education_fields('+ room +');"> <i class="fas fa-trash-alt"></i> </button>';
		    
		    objTo.appendChild(divtest);
		    $("#package_count").val(room);
		}
		function remove_education_fields(rid) {
			$('.removeclass'+rid).remove();
			room--;
			$("#package_count").val(room);
		}

		$(document).on("blur", "#package_weight", function(){
			var current_weight = $(this).val();
			if (parseInt(current_weight) > 150) {
				$(".weight_error").show();
			}else{
				$(".weight_error").hide();
			}
		})

		$(document).on("blur", "#package_length", function(){
			var current_weight = $(this).val();
			if (parseInt(current_weight) > 80) {
				$(".length_error").show();
			}else{
				$(".length_error").hide();
			}
		})

		$(document).on("blur", "#package_width", function(){
			var current_weight = $(this).val();
			if (parseInt(current_weight) > 80) {
				$(".width_error").show();
			}else{
				$(".width_error").hide();
			}
		})

		$(document).on("blur", "#package_height", function(){
			var current_weight = $(this).val();
			if (parseInt(current_weight) > 80) {
				$(".height_error").show();
			}else{
				$(".height_error").hide();
			}
		})

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

		$('input[type=radio][name=consinee_type]').change(function() {
			if (this.value == 'new') {
				$("#existing_consinees").hide();
				$("#new_consinee_details").show();
				$(".new_consignee_hide").hide();
				$('#consignee_id').val('');
				$("#new_consinee_details :input").each(function(){
					$(this).val('');
				});
			}
			else if (this.value == 'existing') {
				$("#new_consinee_details").hide();
				$("#existing_consinees").show();
				$(".new_consignee_hide").show();
			}
		});

		$(document).on("change", "#consignee_id", function(e){
        	e.preventDefault();
			var consignee_id = this.value;
			var name = $(this).find(':selected').data('name');
			var country = $(this).find(':selected').data('country');
			var address = $(this).find(':selected').data('address');
			var zip = $(this).find(':selected').data('zip');
			var city = $(this).find(':selected').data('city');
			var state = $(this).find(':selected').data('state');
			var phone = $(this).find(':selected').data('phone');
			var phone1 = $(this).find(':selected').data('phone2');
			// GET THE Receiver phone code and flag
			var to_phone_1 = document.querySelector("#consignee_phone");
			window.intlTelInput(to_phone_1, {
				allowDropdown: false, 
				initialCountry: country,
				separateDialCode: true,
				utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
			});

			// GET THE Receiver phone code and flag
			var to_phone_2 = document.querySelector("#consignee_homephone");
			window.intlTelInput(to_phone_2, {
				allowDropdown: false, 
				initialCountry: country,
				separateDialCode: true,
				utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
			});
			$("#saved_consignee_details #consignees_id").val(consignee_id).prop('readonly', true);
			$("#new_consinee_details #consignee_name").val(name).prop('readonly', true);
			$("#to_country_code").val(country).change();
			$("#new_consinee_details #to_address").val(address).prop('readonly', true);
			$("#new_consinee_details #to_zip").val(zip).prop('readonly', true);
			$("#new_consinee_details #to_city").val(city).prop('readonly', true);
			$("#new_consinee_details #to_state").val(state).prop('readonly', true);
			$("#new_consinee_details #consignee_phone").val(phone).prop('readonly', true);
			$("#new_consinee_details #consignee_homephone").val(phone1).prop('readonly', true);
			$("#new_consinee_details").show();
			$(".new_consignee_hide").show();
		})

		function selDelLoc(obj){
			delivery_location = $(obj).val();
			$('#delivery_location').val(delivery_location);
			$('.delivery_information').html('');
			$('#get_quote_form').submit();
		}

	</script>
@endsection