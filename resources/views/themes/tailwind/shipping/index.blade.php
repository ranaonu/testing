@extends('theme::layouts.app')

@section('content')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />
<main class="Shipping-layout">
	<div class="container max-w-7xl">
		<div class="content">
			<form class="get_quote_form" id="get_quote_form">
				<div class="row">
					<div class="col-lg-12">
						<label class="form-label">Pull returning customer <!-- <span class="required">*</span> --></label>      
						<div class="input-group">
							<input type="text" class="form-control" placeholder="Enter Phone number, email or account #" name="user_info">
							<span class="input-group-btn">
								<button class="btn" type="submit">Load</button>
							</span>
						</div>
					</div>
				</div>	
	        </form>
	    </div>
		<form class="get_quote_form" id="do_shipment_form">
			<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
			<input type="hidden" name="quote_id" value="{{$shipping_page_info['quote_id']}}">
			<input type="hidden" name="partner" value="{{$shipping_page_info['partner']}}">
			<div class="row">
				<div class="col-lg-12">
					<div class="form-card">
						<div class="form-head">
							<h3>Shipper Information:</h3>
						</div>
						<div class="form-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label class="form-label">Name <span class="required">*</span></label>                       
										<input type="text" class="form-control disable_delivery_info" required name="from_name" id="from_name" value="{{ Auth::user()->name }}">
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label class="form-label">Country <span class="required">*</span></label>
										<input type="hidden" name="from_country_name" id="from_country_name" value="{{$shipping_page_info['shipper_country']}}" class="form-control disable_delivery_info">

										<select class="form-control form-select disable_delivery_info msDropdown" readonly required name="from_country" id="from_country_code" onChange="getCountry(this.value, 'from')">
											<option disabled selected value> -- Select country -- </option>
											@foreach ($countries as $country)
												<option value="{{$country->alpha_2_code}}" {{(isset($shipping_page_info['shipper_country']) && $shipping_page_info['shipper_country'] == $country->country_name) ? 'selected':''}} data-from_zip_supported={{$country->zip_code_supported}} data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}" >{{$country->country_name}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-lg-7">
									<div class="form-group">
										<label class="form-label">Address <span class="required">*</span></label>                                   
										<input type="text" class="form-control disable_delivery_info" required name="from_address" id="from_address" value="{{(isset($shipping_page_info['shipper_addressLine1']) && !empty($shipping_page_info['shipper_addressLine1']) && isset($shipping_page_info['shipper_addressLine2']) && !empty($shipping_page_info['shipper_addressLine2'])) ? $shipping_page_info['shipper_addressLine1'].' '.$shipping_page_info['shipper_addressLine2']:''}}" readonly >
									</div>
								</div>
								<div class="col-lg-2">
									<div class="form-group">
										<label class="form-label">Apt/Ste/Unit <!-- <span class="required">*</span> --></label>                        
										<input type="text" class="form-control disable_delivery_info" name="from_apt" id="from_apt">
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label class="form-label" id="from_zip_code">Zip Code <span class="required">*</span></label>                       
										<input type="text" class="form-control disable_delivery_info" required name="from_zip"  id="from_zip" value="{{(isset($shipping_page_info['shipper_pin']) && !empty($shipping_page_info['shipper_pin'])) ? $shipping_page_info['shipper_pin']:''}}" readonly '>
									</div>
								</div>
								<div class="col-lg-5">
									<div class="form-group">
										<label class="form-label">City <span class="required">*</span></label>                        
										<input type="text" class="form-control disable_delivery_info" required name="from_city" id="from_city" value="{{(isset($shipping_page_info['shipper_city']) && !empty($shipping_page_info['shipper_city'])) ? $shipping_page_info['shipper_city']:''}}" readonly '>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-label">State <span class="required">*</span></label>                       
										<input type="text" class="form-control disable_delivery_info" required name="from_state" id="from_state" value="{{(isset($shipping_page_info['shipper_state']) && !empty($shipping_page_info['shipper_state'])) ? $shipping_page_info['shipper_state']:''}}" readonly>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Phone <span class="required">*</span></label>                        
										<input type="text" class="form-control disable_delivery_info" required name="from_phone" id="from_phone" placeholder="Enter phone" value="{{ Auth::user()->shipper_phone }}">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Email <span class="required">*</span></label>                       
										<input type="email" class="form-control disable_delivery_info" required name="from_email"  id="from_email" value="{{ Auth::user()->email }}">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-12">
					<div class="form-card">
						<div class="form-head">
							<h3>Consignee Information:</h3>
						</div>
						<input type="hidden" name="consignee_id" value="{{(isset($consignee_data['id']) && !empty($consignee_data['id'])) ? $consignee_data['id']:''}}">
						<div class="form-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label class="form-label">Name <span class="required">*</span></label>                       
										<input type="text" class="form-control disable_delivery_info" required name="to_name" id="to_name" value="{{(isset($consignee_data['consignee_name']) && !empty($consignee_data['consignee_name'])) ? $consignee_data['consignee_name']:''}}">
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label class="form-label">Country <span class="required">*</span></label>
										<input type="hidden" name="to_country_name" id="to_country_name" class="form-control disable_delivery_info" value="{{$shipping_page_info['receiver_country']}}">
										<select class="form-control form-select disable_delivery_info msDropdown" required name="to_country" id="to_country_code" onChange="getCountry(this.value, 'to')" readonly>
											<option disabled selected value> -- Select country -- </option>
											@foreach ($countries as $country)
												<option value="{{$country->alpha_2_code}}" data-to_zip_supported={{$country->zip_code_supported}} data-image="{{ asset('themes/' . $theme->folder . '/images/flags/'.strtolower($country->alpha_2_code).'.png') }}" {{(isset($shipping_page_info['receiver_country']) && $shipping_page_info['receiver_country'] == $country->country_name) ? 'selected':''}}>{{$country->country_name}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-lg-9">
									<div class="form-group">
										<label class="form-label">Address <span class="required">*</span></label>                                   
										<input type="text" class="form-control disable_delivery_info required" name="to_address" id="to_address" value="{{(isset($shipping_page_info['receiver_addressLine1']) && !empty($shipping_page_info['receiver_addressLine1']) && isset($shipping_page_info['receiver_addressLine2']) && !empty($shipping_page_info['receiver_addressLine2'])) ? $shipping_page_info['receiver_addressLine1'].' '.$shipping_page_info['receiver_addressLine2']:''}}" readonly>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label class="form-label" id="to_zip_code">Zip Code <!-- <span class="required">*</span> --></label>                       
										<input type="text" class="form-control disable_delivery_info required" name="to_zip" id="to_zip" value="{{(isset($shipping_page_info['receiver_pin']) && !empty($shipping_page_info['receiver_pin'])) ? $shipping_page_info['receiver_pin']:''}}" readonly>
									</div>
								</div>
								<div class="col-lg-5">
									<div class="form-group">
										<label class="form-label">City <span class="required">*</span></label>                        
										<input type="text" class="form-control disable_delivery_info required" name="to_city" id="to_city" value="{{(isset($shipping_page_info['receiver_city']) && !empty($shipping_page_info['receiver_city'])) ? $shipping_page_info['receiver_city']:''}}" readonly>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-label">State <span class="required">*</span></label>                       
										<input type="text" class="form-control disable_delivery_info required" name="to_state" id="to_state" value="{{(isset($shipping_page_info['receiver_state']) && !empty($shipping_page_info['receiver_state'])) ? $shipping_page_info['receiver_state']:''}}" readonly>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Cell Phone <span class="required">*</span></label>                       
										<input type="text" class="form-control disable_delivery_info required" required name="to_phone_1" id="to_phone_1" placeholder="Enter phone" value="{{(isset($consignee_data['consignee_phone']) && !empty($consignee_data['consignee_phone'])) ? $consignee_data['consignee_phone']:''}}">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Home/Business Phone: <!-- <span class="required">*</span> --></label>                       
										<input type="text" class="form-control disable_delivery_info required" name="to_phone_2" id="to_phone_2" placeholder="Enter phone" value="{{(isset($consignee_data['consignee_homephone']) && !empty($consignee_data['consignee_homephone'])) ? $consignee_data['consignee_homephone']:''}}">
									</div>
								</div>
								<div class="col-lg-12" style="display: none;">
									<div class="form-group">
										<label class="form-label">Email <span class="required">*</span></label>                       
										<input type="email" class="form-control disable_delivery_info required" required name="to_email" id="to_email" value="consignee@zionshipping.com">
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
						<div class="form-body">
							<div class="row">
								<div class="col-lg-4">
									<label class="form-label">Flat rate items?</label>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" name="flat_rate" class="custom-control-input disable_delivery_info" id="customCheck1" readonly {{(isset($shipping_page_info['package_type']) && $shipping_page_info['package_type'] == 'document') ? 'checked':''}} '>
									</div>
								</div>
								<div class="col-lg-8">
									<div class="form-group">
										<label class="form-label">No. of Packages<span class="required">*</span></label>
										<select class="form-control form-select disable_delivery_info required" name="package_count" id="package_count" readonly>
											@for ($i = 1; $i <= 25; $i++)
										        <option value="{{ $i }}" {{(isset($shipping_page_info['total_packages']) && $shipping_page_info['total_packages'] == $i) ? 'selected':''}}>{{ $i }}</option>
										    @endfor
										</select>
									</div>
								</div>
							</div>
							@if(isset($shipping_page_info['package_type']) && $shipping_page_info['package_type'] == 'document')
								<div class="row" id="Shipment_type">
									<div class="col-lg-12">
										<div class="form-group">
											<select class="form-control form-select disable_delivery_info required" readonly required name="shipment_type" id="Shipment_type_dropdown">
												<option disabled selected value> -- Select Shipment Type -- </option>
												<option value="contains_document" selected>Document</option>
											</select>
										</div>
									</div>
								</div>
							@endif
							<div class="panel panel-default mb-4">
								<div class="panel-body">
									<ul id="education_fields" class="pkg-info-list">
										@if(isset($shipping_page_info['total_packages']) && $shipping_page_info['total_packages'] > 0)
											@for ($i=0; $i < $shipping_page_info['total_packages']; $i++) 
							                    <li>
													<div class="form-group">
														<input type="text" class="form-control disable_delivery_info" required name="dimensions[weight][]" placeholder="Weight in lbs" value="{{$shipping_page_info['packages'][$i]['weight']}}" readonly> 
													</div>
													<div class="form-group">
														<input type="text" class="form-control disable_delivery_info" required name="dimensions[length][]" placeholder="Length" value="{{$shipping_page_info['packages'][$i]['length']}}" readonly>
													</div>
													<div class="form-group">
														<input type="text" class="form-control disable_delivery_info" required name="dimensions[width][]" placeholder="Width" value="{{$shipping_page_info['packages'][$i]['width']}}" readonly>
													</div>
													<div class="form-group">
														<input type="text" class="form-control disable_delivery_info" required name="dimensions[height][]" placeholder="Height" value="{{$shipping_page_info['packages'][$i]['height']}}" readonly>
													</div>
													<!-- <button class="btn btn-success disable_delivery_info" type="button" onclick="education_fields();"> 
													<i class="fas fa-plus"></i> </button> -->
												</li>
										    @endfor
										@else
											<li>
												<div class="form-group">
													<input type="text" class="form-control disable_delivery_info" required name="dimensions[weight][]" placeholder="Weight in lbs">
												</div>
												<div class="form-group">
													<input type="text" class="form-control disable_delivery_info" required name="dimensions[length][]" placeholder="Length">
												</div>
												<div class="form-group">
													<input type="text" class="form-control disable_delivery_info" required name="dimensions[width][]" placeholder="Width">
												</div>
												<div class="form-group">
													<input type="text" class="form-control disable_delivery_info" required name="dimensions[height][]" placeholder="Height">
												</div>
												<button class="btn btn-success disable_delivery_info" type="button" onclick="education_fields();"> 
												<i class="fas fa-plus"></i> </button>
											</li>
										@endif
									</ul>
								</div>
							</div>
							<div class="form-group">
								<label class="form-label">Total Value <span class="required">*</span></label>
								<input type="text" class="form-control disable_delivery_info" required name="total_value" placeholder="100" value="{{(isset($shipping_page_info['package_value']) && !empty($shipping_page_info['package_value'])) ? $shipping_page_info['package_value']:''}}" readonly>
							</div>
							@if($shipping_page_info['package_value'] > 2500)
								<div class="form-group">
									<label class="form-label">ITN number <span class="required">*</span></label>
									<input type="text" class="form-control disable_delivery_info" required name="itn_number" placeholder="ABC-123" required>
									<strong>Note:</strong><p>You can get your ITN number from this website > <a href="https://www.census.gov/foreign-trade/aes/aesdirect/transitiontoace.html" target="_blank">https://www.census.gov/foreign-trade/aes/aesdirect/transitiontoace.html</a></p>
								</div>
							@endif
							<div class="form-group">
								<label class="form-label">Description <span class="required">{{($shipping_page_info['shipper_country'] == $shipping_page_info['receiver_country'])?'':'*'}}</span></label>
								<textarea type="text" class="form-control disable_delivery_info form-textarea" {{($shipping_page_info['shipper_country'] == $shipping_page_info['receiver_country'])?'':'required'}} id="package_description" name="package_description" placeholder="Put the complete package description. This is mandatory for customs."></textarea>
								<div class="alert alert-danger result_not_found" style="display: none;"><p>The description is incomplete and will not be accepted by customs.  Please enter a detailed description of the contents being shipped.</p></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="form-card">
						<div class="form-head">
							<h3>Pickup Information </h3>
						</div>
						<div class="form-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label class="form-label">Do you need pickup for this shipment? <!-- <span class="required">*</span> --></label>
										<select class="form-control form-select disable_delivery_info" required name="pickup_required" id="pickup_required" >
											<option value disabled>Select</option>
											<option value="no" selected>No</option>
											<option value="yes">Yes</option>
										</select>

									</div>
								</div>
								<div id="pickup_required_block" style="display: none;">
									<div class="col-lg-12">
										<div class="form-group">
											<label class="form-label">Where should the courier pick up the shipment? <span class="required">*</span></label>
											<select name="pick_location" id="pick_location" class="form-control form-select disable_delivery_info required" required>
												<option value="Reception">Reception</option>
												<option value="Back Door">Back Door</option>
												<option value="Front Door">Front Door</option>
												<option value="Loading Dock">Loading Dock</option>
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
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="form-card">
						<div class="form-head">
							<h3>Delivery:</h3>
						</div>
						<div class="form-body">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										@if($shipping_page_info['partner'] == 'DHL')
											<input type="hidden" name="product_code" value="" id="product_code">
										@endif
										<input type="hidden" name="dhl">
										<input type="hidden" name="deliveryEstimatePrice" id="deliveryEstimatePrice" value="">
										<label class="form-label">Delivery Option <span class="required">*</span></label>
										<select class="form-control form-select disable_delivery_info msDropdown" required name="delivery_option" id="delivery_option" >
											<option value disabled>Select</option>
											@php
												$count = 0;
											@endphp
											@if($shipping_page_info['shippers'])
												@foreach($shipping_page_info['shippers'] as $shippers)
													@if($shipping_page_info['partner'] == 'DHL')
														<option value="{{$shippers['product_type']}}" {{(isset($shipping_page_info['selected_shipper']) && ($shipping_page_info['selected_shipper'] == $shippers['product_name']))?'selected':''}} data-deliveryEstimateDate="{{(isset($shippers['estimatedDeliveryDate']))?$shippers['estimatedDeliveryDate']:''}}" data-deliveryEstimateTime="{{(isset($shippers['estimatedDeliveryTime']))?$shippers['estimatedDeliveryTime']:''}}" data-deliveryEstimatePrice="{{(isset($shippers['product_price']))?$shippers['product_price']:''}}" data-deliveryProductCode="{{(isset($shippers['product_code']))?$shippers['product_code']:''}}">{{$shippers['product_name']}}</option>
													@else
														<option value="{{$shippers['product_type']}}" {{(isset($shipping_page_info['selected_shipper']) && ($shipping_page_info['selected_shipper'] == $shippers['product_name']))?'selected':''}} data-deliveryEstimateDate="{{(isset($shippers['estimatedDeliveryDate']))?$shippers['estimatedDeliveryDate']:''}}" data-deliveryEstimateTime="{{(isset($shippers['estimatedDeliveryTime']))?$shippers['estimatedDeliveryTime']:''}}" data-deliveryEstimatePrice="{{(isset($shippers['product_price']))?$shippers['product_price']:''}}">{{$shippers['product_name']}}</option>
													@endif
												@endforeach
											@else
												<option value="DHL Express">DHL Express</option>
											@endif
										</select>
										<p class="estimation" id="estimation"></p>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Delivery Location <span class="required">*</span></label>
										<select class="form-control form-select disable_delivery_info msDropdown" required name="delivery_location" id="delivery_location" >
											<option value>Select</option>
											<option value="Home Delivery" selected>Home Delivery</option>
										</select>

									</div>
								</div>
								<div class="col-lg-12">
									<label class="form-label">Instruction <!-- <span class="required">*</span> --></label>
									<textarea rows="4" cols="50" type="text" class="form-control form-textarea disable_delivery_info" name="delivery_description" placeholder="Put the complete package description. This is mandatory for customs."></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="form-card">
						<div class="form-head">
							<h3>Payment:</h3>
						</div>
						<div class="form-body">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Payent Option <span class="required">*</span></label>
										<input type="hidden" name="delivery-option" id="delivery_option" class="form-control disable_delivery_info">
										<select class="form-control form-select disable_delivery_info msDropdown" required name="payment_type" id="from_country_code" >
											<option value=""> -- Select the Payment Option -- </option>
											<option value="CREDIT OR DEBIT CARD">CREDIT OR DEBIT CARD</option>
											<option value="PAYPAL">PAYPAL</option>
											<option value="SPLIT PAYMENT">SPLIT PAYMENT (Available for selected countries)</option>
											<option value="COLLECT">COLLECT (Available for selected countries)</option>
											<option value="Paid at ZION SHIPPING TRUKING DEPT.">Paid at ZION SHIPPING TRUKING DEPT. </option>
											<!-- <option value="Credit or Debit Card">Credit or Debit Card </option> -->
										</select>

									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label class="form-label">Coupon/Promo Code<span class="required"></span></label>
										<div class="track-input" data-dashlane-rid="456648454bd978f3" data-form-type="other">
											<input type="text" class="form-control" data-dashlane-rid="9003aae30a21933a" data-form-type="other" name="promo">
											<button type="submit" class="track-btn btn" data-dashlane-rid="ed07a78e3f185e5a" data-form-type="other" data-dashlane-label="true">Apply</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6 text-left">
					<div class="btn-wrap text-center">
						<a href="{{url('get-quote/'.$shipping_page_info['quote_id'])}}" class="cstm-btn quote-btn">Go back to quote</a>
					</div>
				</div>
				<div class="col-md-6 text-right" id="complete_shipment_btn">
					<div class="btn-wrap text-center">
						<button type="submit" class="cstm-btn quote-btn">Complete Shipment</button>
					</div>
				</div>
			</div>
		</form>
		<div id="loader_image">
	        <img id="loading-image" src="{{ asset('themes/' . $theme->folder . '/images/loaders/searching.gif') }}" style="display:none; height: 500px!important;     margin-left: auto; margin-right: auto; width: 50%;"/>
	    </div>
		<div class="row shipping_information">
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

		var current_partner = "<?php echo $shipping_page_info['partner']?>";

		var c_minutes = '';
		var new_day = false;
			
		// GET THE AREA CODE AND FLAG
	    var from_phone = document.querySelector("#from_phone");
	    window.intlTelInput(from_phone, {
	      	allowDropdown: false, 
		  	initialCountry: "<?php echo $shipping_page_info['shipper_countryCode'] ?>",
			separateDialCode: true,
	      	utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
	    });

	    // GET THE Receiver phone code and flag
	    var to_phone_1 = document.querySelector("#to_phone_1");
	    window.intlTelInput(to_phone_1, {
	      	allowDropdown: false, 
		  	initialCountry: "<?php echo $shipping_page_info['receiver_countryCode'] ?>",
			separateDialCode: true,
	      	utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
	    });

	    // GET THE Receiver phone code and flag
	    var to_phone_2 = document.querySelector("#to_phone_2");
	    window.intlTelInput(to_phone_2, {
	      	allowDropdown: false, 
		  	initialCountry: "<?php echo $shipping_page_info['receiver_countryCode'] ?>",
			separateDialCode: true,
	      	utilsScript: "<?php url('themes/tailwind/js/utils.js');?>",
	    });


		$(window).ready(function(){
			var deliveryEstimateDate 	= $("#delivery_option").find(':selected').attr('data-deliveryEstimateDate');
			var deliveryEstimateTime 	= $("#delivery_option").find(':selected').attr('data-deliveryEstimateTime');
			var deliveryEstimatePrice 	= $("#delivery_option").find(':selected').attr('data-deliveryEstimatePrice');
			if (deliveryEstimatePrice != '') {
				$("#deliveryEstimatePrice").val(deliveryEstimatePrice);
			}
			if (current_partner == 'DHL') {
				var deliveryProductCode = $("#delivery_option").find(':selected').attr('data-deliveryProductCode');
				$("#product_code").val(deliveryProductCode);
			}
			$("#estimation").html("<b>Estimate Delivery By: "+deliveryEstimateDate+" at "+deliveryEstimateTime+", in $"+deliveryEstimatePrice+"</b>");
		})

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
		
		function initialize_slider(passed_minutes='') {
			if (passed_minutes == '') {
				c_minutes = (get_minutes())?get_minutes():600
				if ((parseInt(1020) - parseInt(c_minutes)) <= 60 ) {
					c_minutes = 600;
					var next_date = new Date();
					next_date.setDate(next_date.getDate() + 1);
					var ad_month = parseInt(next_date.getMonth())+parseInt(1);
					if (parseInt(ad_month) < parseInt(10)) {
						ad_month = "0"+ad_month;
					}
					var ad_date = next_date.getDate();
					if (parseInt(ad_date) < parseInt(10)) {
						ad_date = "0"+ad_date;
					}
					var t_date = next_date.getFullYear()+"-"+ad_month+"-"+ad_date;
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
			
			var from_zip_supported = $("#from_country_code").find(':selected').data('from_zip_supported');
			if (from_zip_supported == 'Y') {
				$("#from_zip_code").html("Zip Code <span class='required'>*</span>");
			}else{
				$("#from_zip_code").html("Suburb <span class='required'>*</span>");
			}
		
			var to_zip_supported = $("#to_country_code").find(':selected').data('to_zip_supported');
			if (to_zip_supported == 'Y') {
				$("#to_zip_code").text("Zip Code");
			}else{
				$("#to_zip_code").text("Suburb");
			}
			
			initialize_slider();
			$("#pickup_date").on("change", function(){
				if ($(this).val() == curDate) {
					initialize_slider();
				}else{
					initialize_slider(600);
				}
			});

			$("#pickup_required").on("change", function(){
				if ($(this).val() == 'yes') {
					$("#pickup_required_block").fadeIn(1000);
				}else{
					$("#pickup_required_block").fadeOut(500);
				}
			})

			$("#delivery_option").on("change", function(){
				var deliveryEstimateDate 	= $("#delivery_option").find(':selected').attr('data-deliveryEstimateDate');
				var deliveryEstimateTime 	= $("#delivery_option").find(':selected').attr('data-deliveryEstimateTime');
				var deliveryEstimatePrice 	= $("#delivery_option").find(':selected').attr('data-deliveryEstimatePrice');
				if (current_partner == 'DHL') {
					var deliveryProductCode = $("#delivery_option").find(':selected').attr('data-deliveryProductCode');
					$("#product_code").val(deliveryProductCode);
				}
				$("#estimation").html("<b>Estimate Delivery By: "+deliveryEstimateDate+" at "+deliveryEstimateTime+", in $"+deliveryEstimatePrice+"</b>");
			})
		
			$("#package_description").on("blur", function(){
				if (current_partner == 'FEDEX') {
					var package_description_val = $("#package_description").val();
					if (package_description_val.split(' ').length < 2) {
						// alert("The description is incomplete and will not be accepted by customs.  Please enter a detailed description of the contents being shipped.");
						$(".result_not_found").show();
						$("#complete_shipment_btn").hide();
					}else{
						$(".result_not_found").hide();
						$("#complete_shipment_btn").show();
					}
				}
			})

				

			$(".msDropdown").msDropdown();
			$("form#do_shipment_form").on("submit", function(e){
				e.preventDefault();
				$('html, body').animate({
			        scrollTop: $("#loader_image").offset().top
			    }, 2000);
				$.ajax({
					type: "POST",
					url: '/update-consignee',
					data: $(this).serialize(),
					dataType: 'JSON',
					success: function( response ) {

					}
				});
				$.ajax({
		           	type: "POST",
		           	url: '/place-shipment',
		           	data: $("form#do_shipment_form").serialize(),
		           	dataType: 'JSON',
		           	beforeSend: function() {
		              	$("#loading-image").attr('src', "{{ asset('themes/' . $theme->folder . '/images/loaders/searching.gif') }}");
		           		$("#loading-image").show();
		           	},
					success: function( response ) {
						$("#loading-image").attr('src', "{{ asset('themes/' . $theme->folder . '/images/loaders/confirmBox.gif') }}");
		              	setTimeout(function () {
	                    	$(".shipping_information").html(response.html);
							$("#do_shipment_form").html("");
							$("#loading-image").hide();
	                    }, 3000);
						//if (response.status == 'success') {
							
						//}
						console.log("response::"+response);
					}
		       });
			})
		})
		// let unavailableDates = ["01-04-2022", "04-04-2022", "05-04-2022", "06-04-2022"];
		// function isUnavailable(date) {
		// 	// console.log("in date"+date+"=="+date.getDate()+"-"+(date.getMonth() + 1)+"-"+date.getFullYear());
		// 	var date_from_picker = date.getDate();
		// 	if (parseInt(date_from_picker) < parseInt(10)) {
		// 		date_from_picker = "0"+date_from_picker;
		// 	}
		// 	var month_from_picker = parseInt(date.getMonth()) + parseInt(1);
		// 	if (parseInt(month_from_picker) < parseInt(10)) {
		// 		month_from_picker = "0"+month_from_picker;
		// 	}
		// 	var complete_date = date_from_picker+"-"+month_from_picker+"-"+date.getFullYear();
		// 	console.log("complete_date :: "+complete_date);
		// 	if ($.inArray(complete_date, unavailableDates)) {
		// 		console.log("in if");
		// 		return false;
		// 	}else{
		// 		console.log("in else");
		// 		return true;
		// 	}
		// }

		// var dates = ["01-04-2022", "04-04-2022", "05-04-2022", "06-04-2022"];
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

		$(document).on("click", "#label_btn a", function(){
			$("#invoice_btn").find('a').removeClass('inactiveLink');
		})
		$(document).on("click", "#invoice_btn a", function(){
			$("#receipt_btn").find('a').removeClass('inactiveLink');
		})

	</script>
@endsection