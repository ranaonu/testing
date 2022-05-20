@extends('theme::layouts.app')
@section('content')
<section class="about-us" style="padding-top:10px;">
    <h1 class="max-w-md text-4xl font-extrabold text-gray-900 sm:mx-auto lg:max-w-none lg:text-5xl sm:text-center pb-10">Checkout</h1>
    <div class="container max-w-7xl">
      
      <div class="row">
            
        <div class="products mb-3">
            <div class="page-content">
            	<div class="checkout1">
	                <div class="container">
            			
            			<form action="{{route('wave.officeSupplyPlaceOrder')}}" method="post">
                            @csrf
		                	<div class="row">
		                		<div class="col-lg-9">
                                    <div class="form-card">
                                        <div class="form-head">
		                			        <h3>Shipping Details</h3><!-- End .checkout-title -->
                                        </div>
                                        <div class="form-body">
											<?php
											$first_name = '';
											$last_name = '';
											$company_name = '';
											$address1 = '';
											$zip_code = '';
											$to_city = '';
											$to_state = '';
											$phone = '';
											$email = '';
											if(isset($address->first_name)){
												$first_name = $address->first_name;
												$last_name = $address->last_name;
											}else{
												$pickup_name = auth()->user()->name;
												$parts = explode(" ", $pickup_name);
												if(count($parts) > 1) {
													$last_name = array_pop($parts);
													$first_name = implode(" ", $parts);
												}else{
													$first_name = $pickup_name;
													$last_name = "";
												}
											}
											if(isset($address->company_name)){
												$company_name = $address->company_name;
											}
											if(isset($address->address1)){
												$address1 = $address->address1;
											}
											if(isset($address->zip)){
												$zip_code = $address->zip;
											}
											if(isset($address->city)){
												$to_city = $address->city;
											}
											if(isset($address->state)){
												$to_state = $address->state;
											}
											if(isset($address->phone)){
												$phone = $address->phone;
											}
											if(isset($address->email)){
												$email = $address->email;
											}
											?>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <label class="form-label">First Name *</label>
                                                    <input type="text" name="first_name" class="form-control" value="{{$first_name}}" required>
                                                </div><!-- End .col-sm-6 -->

                                                <div class="col-sm-6">
                                                    <label class="form-label">Last Name *</label>
                                                    <input type="text" name="last_name" class="form-control" value="{{$last_name}}" required>
                                                </div><!-- End .col-sm-6 -->
                                            </div><!-- End .row -->

	            						    <label class="form-label">Company Name (Optional)</label>
	            						    <input type="text" name="company_name" class="form-control" value="{{$company_name}}">

	            						
										    <label class="form-label">Country <span class="required">*</span></label>
										    <input type="hidden" name="to_country_name" id="to_country_name" class="form-control disable_delivery_info" value="{{(isset($quote_request['to_country_name']) && !empty($quote_request['to_country_name']))?$quote_request['to_country_name']:''}}">
										    <select class="form-control form-select disable_delivery_info msDropdown" required name="to_country" id="to_country_code" onChange="getCountry(this.value, 'to')">
											    <option disabled selected value> -- Select country -- </option>
                                                @foreach ($countries as $country)
                                                    <option value="{{$country->alpha_2_code}}" data-to_zip_supported={{$country->zip_code_supported}} data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}" {{(isset($quote_request['to_country']) && $quote_request['to_country'] == $country->alpha_2_code)?'selected':''}}>{{$country->country_name}}</option>
                                                @endforeach
										    </select>
								
										    <label class="form-label">Address <span class="required">*</span></label>                                   
										    <input type="text" class="form-control disable_delivery_info required" name="to_address" id="to_address" placeholder="Enter a location" value="{{$address1}}">
									
								
										    <label class="form-label" id="to_zip_code">Zip Code <!-- <span class="required">*</span> --></label>                       
										    <input type="text" class="form-control disable_delivery_info required" name="to_zip" id="to_zip" value="{{$zip_code}}">
									
										    <label class="form-label">City <span class="required">*</span></label>                        
										    <input type="text" class="form-control disable_delivery_info required" name="to_city" id="to_city" value="{{$to_city}}">
									
										    <label class="form-label">State <span class="required">*</span></label>                       
										    <input type="text" class="form-control disable_delivery_info required" name="to_state" id="to_state"  value="{{$to_state}}">
									

		                				    <div class="row">
		                					

                                                <div class="col-sm-6">
                                                    <label class="form-label">Phone *</label>
                                                    <input type="tel" name="phone" class="form-control" value="{{$phone}}" required>
                                                </div><!-- End .col-sm-6 -->
		                				    </div><!-- End .row -->

	                					    <label class="form-label">Email address *</label>
	        							    <input type="email" name="email" class="form-control" value="{{$email}}" required>
                                        </div>
                                    </div>

                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="checkout-diff-address" name="checkout-diff-address">
                                        <label class="custom-control-label" for="checkout-diff-address">Same as shipping address.</label>
                                    </div><!-- End .custom-checkbox -->
                                    <div id="billing-address">
                                        <div class="form-card">
                                            <div class="form-head">
                                                <h3>Billing Details</h3><!-- End .checkout-title -->
                                            </div>
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <label class="form-label">First Name *</label>
                                                        <input type="text" name="billing_first_name" class="form-control req" required>
                                                    </div><!-- End .col-sm-6 -->

                                                    <div class="col-sm-6">
                                                        <label class="form-label">Last Name *</label>
                                                        <input type="text" name="billing_last_name" class="form-control req" required>
                                                    </div><!-- End .col-sm-6 -->
                                                </div><!-- End .row -->

                                                <label class="form-label">Company Name (Optional)</label>
                                                <input type="text" class="form-control" name="billing_company_name">

                                                <label class="form-label">Country <span class="required">*</span></label>
                                                <input type="hidden" name="bill_country_name" id="bill_country_name" class="form-control disable_delivery_info" value="{{(isset($quote_request['bill_country_name']) && !empty($quote_request['bill_country_name']))?$quote_request['bill_country_name']:''}}">
                                                <select class="form-control form-select disable_delivery_info msDropdown" name="bill_country" id="bill_country_code" onChange="getCountry(this.value, 'to')">
                                                    <option disabled selected value> -- Select country -- </option>
                                                    @foreach ($countries as $country)
                                                        <option value="{{$country->alpha_2_code}}" data-to_zip_supported={{$country->zip_code_supported}} data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}" {{(isset($quote_request['bill_country']) && $quote_request['bill_country'] == $country->alpha_2_code)?'selected':''}}>{{$country->country_name}}</option>
                                                    @endforeach
                                                </select>

                                                <label class="form-label">Address *</label>
                                                <input type="text" class="form-control req" placeholder="Address" name="billing_address" required>
                                                

                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <label class="form-label">City *</label>
                                                        <input type="text" name="billing_city" class="form-control req" required>
                                                    </div><!-- End .col-sm-6 -->

                                                    <div class="col-sm-6">
                                                        <label class="form-label">State *</label>
                                                        <input type="text" name="billing_state" class="form-control req" required>
                                                    </div><!-- End .col-sm-6 -->
                                                </div><!-- End .row -->

                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <label class="form-label">Postcode / ZIP *</label>
                                                        <input type="text" name="billing_zip" class="form-control req" required>
                                                    </div><!-- End .col-sm-6 -->

                                                    <div class="col-sm-6">
                                                        <label class="form-label">Phone *</label>
                                                        <input type="tel" class="form-control req" name="billing_phone" required>
                                                    </div><!-- End .col-sm-6 -->
                                                </div><!-- End .row -->

                                                <label class="form-label">Email address *</label>
                                                <input type="email" class="form-control req" name="billing_email" required>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
		                		</div><!-- End .col-lg-9 -->
                                
		                		<aside class="col-lg-3">
		                			<div class="summary" style="margin:15px 0px;">
		                				<h3 class="summary-title">Your Order</h3><!-- End .summary-title -->

		                				<table class="table table-summary">
		                					<thead>
		                						<tr>
		                							<th>Product</th>
		                							<th>Total</th>
		                						</tr>
		                					</thead>

		                					<tbody>
                                            <?php
                                            $total = 0;
                                            ?>
                                            @foreach($cart as $c)
                                                <?php
                                                $subtotal = $c['price'] * $c['quantity'];
                                                $total = $total + $subtotal;
                                                ?>
		                						<tr>
		                							<td><a href="#">{{$c['name']}}</a> x {{$c['quantity']}}</td>
		                							<td>${{number_format($subtotal,2,'.','')}}</td>
		                						</tr>

		                					@endforeach
		                						<tr class="summary-subtotal">
		                							<td>Subtotal:</td>
		                							<td>$<span id="sub-total">{{number_format($total,2,'.','')}}</span></td>
		                						</tr><!-- End .summary-subtotal -->
		                						<tr>
		                							<td>Shipping:</td>
		                							<td id="shipping_fee"></td>
		                						</tr>
		                						<tr class="summary-total">
		                							<td>Total:</td>
		                							<td>
                                                        $<span id="grand-total">{{number_format($total,2,'.','')}}</span>
                                                    </td>
		                						</tr><!-- End .summary-total -->
		                					</tbody>
		                				</table><!-- End .table table-summary -->

		                				<button type="submit" class="btn btn-outline-primary-2 btn-order btn-block">
		                					<span class="btn-text">Place Order</span>
		                					<span class="btn-hover-text">Place Order</span>
		                				</button>
		                			</div><!-- End .summary -->
		                		</aside><!-- End .col-lg-3 -->
		                	</div><!-- End .row -->
            			</form>
	                </div><!-- End .container -->
                </div><!-- End .checkout -->
            </div><!-- End .page-content -->
            </div><!-- End .page-content -->
            
        </div>
       </div>
    </div>
</section>
<script src="{{url('assets/js/jquery.min.js')}}"></script>
<script>
$(function(){
    $('#checkout-diff-address').change(function(){
        if($('#checkout-diff-address').prop('checked')){
            $('#billing-address').hide();
            $('.req').each(function(){
                $(this).removeAttr('required');
            });
        }else{
            $('#billing-address').show();
            $('.req').each(function(){
                $(this).attr('required','');
            });
        }
    });

    // $('#to_state').change(function(){
        
    // });
});
</script>
@endsection
@section('javascript')

	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBvqndqKM903bY5xn03L2F0kFrL5B7_3vk&libraries=places" async defer></script>
	<script>
		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		    }
		});
		@if(isset($address->country))
			$("#to_country_code").find('option[value={{$address->country}}]').attr('selected','');
			$("#to_country_name").val($("#to_country_code option:selected").text());
			$("#bill_country_name").val($("#to_country_code option:selected").text());
			$.ajax({
                type: "POST",
                url: '{{route("wave.getShippingFee")}}',
                data: $("form").serialize(),
                dataType: 'JSON',
                beforeSend: function() {
                    $(".overlay").show();
                },
                success: function( response ) {
                    $('#shipping_fee').html(response.shipping_fee);
                    total = parseFloat($('#sub-total').html()) + parseFloat(response.shipping_fee);
                    $('#grand-total').html(total);
                    $(".overlay").hide();
                }
            });
		@endif
      	
		var to_componentForm = {
		  	//street_number: 'short_name',
		  	//route: 'long_name',
		  	to_city: 'long_name',
		  	to_state: 'short_name',
		  	to_zip: 'short_name'
		};
		
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
				
				$("#to_country_name").val($("#to_country_code option:selected").text());
				$("#bill_country_name").val($("#to_country_code option:selected").text());
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
            $.ajax({
                type: "POST",
                url: '{{route("wave.getShippingFee")}}',
                data: $("form").serialize(),
                dataType: 'JSON',
                beforeSend: function() {
                    $(".overlay").show();
                },
                success: function( response ) {
                    $('#shipping_fee').html(response.shipping_fee);
                    total = parseFloat($('#sub-total').html()) + parseFloat(response.shipping_fee);
                    $('#grand-total').html(total);
                    $(".overlay").hide();
                }
            });
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
				$('#bill_country_code').val("US").change();
			})
        })

			$(".msDropdown").msDropdown();
            </script>
@endsection