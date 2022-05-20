@extends('voyager::master')

@section('page_header')
    <h1 class="page-title">
        <i class="{{ 'voyager-logbook' }}"></i>
        @if(isset($edit_data->id))
        	Update Shipping
        @else
        	Add Shipping
        @endif
        <a href="{{route('view_shipment')}}" class="btn btn-success">View Shipment</a>
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
				<div class="panel panel-bordered">
					@if(isset($edit_data->id))
					<form action="{{route('update_shipment', [$edit_data->id])}}" method="post">
					@else
					<form action="{{route('store_shipment')}}" method="post">
					@endif
						@csrf
						<h3>Shipper Information</h3>
						<div class="panel-body">
							<label class="form-label">Shipper Name</label>
							<input type="text" name="shipper_name" class="form-control" value="{{isset($edit_data->shipper_name)?$edit_data->shipper_name:old('shipper_name')}}" required>
						</div>

						<div class="panel-body">
							<label class="form-label">Shipper Phone</label>
							<input type="text" name="shipper_phone" class="form-control" value="{{isset($edit_data->shipper_phone)?$edit_data->shipper_phone:old('shipper_phone')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Shipper Country</label>
							<select class="form-control" name="shipper_country">
								<option value="">Select Country</option>
								@foreach($counttries_data as $country)
								<option <?php if(isset($edit_data->shipper_country) && $country->id == $edit_data->shipper_country){ echo 'selected';}?>  value="{{$country->id}}">{{$country->country_name}}</option>
								@endforeach
							</select>
						</div>

						<div class="panel-body">
							<label class="form-label">Shipper Address</label>
							<textarea class="form-control" name="shipper_address">{{isset($edit_data->shipper_address)?$edit_data->shipper_address:old('shipper_address')}}</textarea>
						</div>

						<div class="panel-body">
							<label class="form-label">Apt, Suite, Unit#</label>
							<input type="text" name="shipper_address_2" class="form-control" value="{{isset($edit_data->shipper_address_2)?$edit_data->shipment_address_2:old('shipper_address_2')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Shipper City</label>
							<input type="text" name="shipper_address_city" class="form-control" value="{{isset($edit_data->shipper_address_city)?$edit_data->shipper_address_city:old('shipper_address_city')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Shipper State</label>
							<input type="text" name="shipper_address_state" class="form-control" value="{{isset($edit_data->shipper_address_state)?$edit_data->shipper_address_state:old('shipper_address_state')}}">
						</div>	

						<div class="panel-body">
							<label class="form-label">Shipper Zip Code</label>
							<input type="text" name="shipper_address_zip_code" class="form-control" value="{{isset($edit_data->shipper_address_zip_code)?$edit_data->shipper_address_zip_code:old('shipper_address_zip_code')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Shipper Email</label>
							<input type="email" name="shipper_email" class="form-control" value="{{isset($edit_data->shipper_email)?$edit_data->shipper_email:old('shipper_email')}}">
						</div>

						<hr>
						<h3>Consignee Information</h3>

						<div class="panel-body">
							<label class="form-label">Consignee Name</label>
							<input type="text" name="consignee_name" class="form-control" value="{{isset($edit_data->consignee_name)?$edit_data->consignee_name:old('consignee_name')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Consignee Phone</label>
							<input type="text" name="consignee_phone" class="form-control" value="{{isset($edit_data->consignee_phone)?$edit_data->consignee_phone:old('consignee_phone')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Consignee Phone 2</label>
							<input type="text" name="consignee_homephone" class="form-control" value="{{isset($edit_data->consignee_homephone)?$edit_data->consignee_homephone:old('consignee_homephone')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Consignee Country</label>
							<select class="form-control" name="consignee_country">
								<option value="">Select Country</option>
								@foreach($counttries_data as $country)
								<option <?php if(isset($edit_data->consignee_country) && $country->id == $edit_data->consignee_country){ echo 'selected';}?>  value="{{$country->id}}">{{$country->country_name}}</option>
								@endforeach
							</select>
						</div>

						<div class="panel-body">
							<label class="form-label">Consignee State</label>
							<input type="text" name="consignee_address_state" class="form-control" value="{{isset($edit_data->consignee_address_state)?$edit_data->consignee_address_state:old('consignee_address_state')}}">
						</div>	

						<div class="panel-body">
							<label class="form-label">Consignee City</label>
							<input type="text" name="consignee_address_city" class="form-control" value="{{isset($edit_data->consignee_address_city)?$edit_data->consignee_address_city:old('consignee_address_city')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Shipper Address</label>
							<textarea class="form-control" name="consignee_address_city">{{isset($edit_data->consignee_address_city)?$edit_data->consignee_address_city:old('consignee_address_city')}}</textarea>
						</div>

						<div class="panel-body">
							<label class="form-label">Shipper Email</label>
							<input type="email" name="consignee_email" class="form-control" value="{{isset($edit_data->consignee_email)?$edit_data->consignee_email:old('consignee_email')}}">
						</div>

						<hr>
						<h3>Package Information</h3>

						<div class="panel-body">
							<label class="form-label">Number Of Packages</label>
							<select class="form-control" name="numb_of_packages">
								<option value="">Select Package</option>
								@php $i=1 @endphp
								@while($i <= 100)
									<option {{isset($edit_data->numb_of_packages) && $edit_data->numb_of_packages == $i ? "selected":""}} value="{{$i}}">{{$i}}</option>
									@php $i++ @endphp
								@endwhile
							</select>
						</div>

						<div class="panel-body">
							<label class="form-label">Weight In Lbs</label>
							<input type="number" name="package_weight" class="form-control" value="{{isset($edit_data->package_weight)?$edit_data->package_weight:old('package_weight')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Volume</label>
							<input type="number" name="package_volume" class="form-control" value="{{isset($edit_data->package_volume)?$edit_data->package_volume:old('package_volume')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Length</label>
							<input type="number" name="package_length" class="form-control" value="{{isset($edit_data->package_length)?$edit_data->package_length:old('package_length')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Width</label>
							<input type="number" name="package_width" class="form-control" value="{{isset($edit_data->package_width)?$edit_data->package_width:old('package_width')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Height</label>
							<input type="number" name="package_height" class="form-control" value="{{isset($edit_data->package_height)?$edit_data->package_height:old('package_height')}}">
						</div>	

						<div class="panel-body">
							<label class="form-label">Package Description</label>
							<textarea class="form-control" name="package_description">{{isset($edit_data->package_description)?$edit_data->package_description:old('package_description')}}</textarea>
						</div>	

						<div class="panel-body">
							<label class="form-label">Package Value</label>
							<input type="text" name="package_value" class="form-control" value="{{isset($edit_data->package_value)?$edit_data->package_value:old('package_value')}}">
						</div>

						<hr>					
						<h3>Delivery Information</h3>

						<div class="panel-body">
							<label class="form-label">Package Pickup</label>
							<select class="form-control" name="package_pickup">
								<option value="">Package Pickup</option>
								<option {{ isset($edit_data->package_pickup) && $edit_data->package_pickup=="I will Drop off my package at Zion Store" ? "selected":"" }} value="I will Drop off my package at Zion Store">I'll Drop off my package at Zion Store</option>
								
								<option {{isset($edit_data->package_pickup) && $edit_data->package_pickup=="I want Zion to Pick up my package" ? "selected":"" }} value="I want Zion to Pick up my package">I want Zion to Pick up my package</option>
							</select>
						</div>

						<div class="panel-body">
							<label class="form-label">Delivery Option</label>
							<select class="form-control" name="delivery_option">
								<option value="" disabled selected>Delivery Option</option>
								<option {{isset($edit_data->delivery_option) && $edit_data->delivery_option == "15 to 20 Days Standard" ? "selected" : ""}} value="15 to 20 Days Standard">15 to 20 Days Standard</option>
								<option {{isset($edit_data->delivery_option) && $edit_data->delivery_option == "10 to 15 Days Standard" ? "selected" : ""}} value="10 to 15 Days Standard">10 to 15 Days Standard</option>
								<option {{isset($edit_data->delivery_option) && $edit_data->delivery_option == "5 to 10 Days Standard" ? "selected" : ""}} value="5 to 10 Days Standard">5 to 10 Days Standard</option>
								<option {{isset($edit_data->delivery_option) && $edit_data->delivery_option == "3 to 5 Days Express" ? "selected" : ""}} value="3 to 5 Days Express">3 to 5 Days Express</option>
								<option {{isset($edit_data->delivery_option) && $edit_data->delivery_option == "2 to 3 Days Express" ? "selected" : ""}} value="2 to 3 Days Express">2 to 3 Days Express</option>
								<option {{isset($edit_data->delivery_option) && $edit_data->delivery_option == "1 to 2 Days Express" ? "selected" : ""}} value="1 to 2 Days Express">1 to 2 Days Express</option>
								<option {{isset($edit_data->delivery_option) && $edit_data->delivery_option == "custome_date" ? "selected" : ""}} value="custome_date">Customize Date</option>
							</select>
						</div>

						<div class="panel-body">
							<label class="form-label">Delivery Location</label>
							<select class="form-control" name="delivery_location">
								<option value="">Delivery Location</option>
								<option {{ isset($edit_data->delivery_location) && $edit_data->delivery_location=="Pickup in Zion Office" ? "selected":"" }} value="Pickup in Zion Office">Pickup in Zion Office</option>
								<option {{ isset($edit_data->delivery_location) && $edit_data->delivery_location=="Home Delivery" ? "selected":"" }} value="Home Delivery">Home Delivery</option>
							</select>
						</div>

						<div class="panel-body">
							<label class="form-label">Online Order</label>
							<select class="form-control" name="online_order">
								<option value="">Select</option>
								<option {{ isset($edit_data->online_order) && $edit_data->online_order=="No" ? "selected":"" }} value="No">No</option>
								<option {{ isset($edit_data->online_order) && $edit_data->online_order=="Yes" ? "selected":"" }} value="Yes">Yes</option>
							</select>
						</div>

						<div class="panel-body">
							<label class="form-label">Special Instructions:</label>
							<textarea class="form-control" name="instruction">{{isset($edit_data->instruction)?$edit_data->instruction:old('instruction')}}</textarea>
						</div>

						<hr>					
						<h3>Charges</h3>

						<div class="panel-body">
							<label class="form-label">Freight Cost</label>
							<input type="text" name="shipment_price" class="form-control" value="{{isset($edit_data->shipment_price)?$edit_data->shipment_price:old('shipment_price')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Pickup Cost</label>
							<input type="text" name="total_pickup_fee" class="form-control" value="{{isset($edit_data->total_pickup_fee)?$edit_data->total_pickup_fee:old('total_pickup_fee')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Delivery Cost</label>
							<input type="text" name="total_delivery_fee" class="form-control" value="{{isset($edit_data->total_delivery_fee)?$edit_data->total_delivery_fee:old('total_delivery_fee')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Insurance Cost</label>
							<input type="text" name="shipment_add_insurance" class="form-control" value="{{isset($edit_data->shipment_add_insurance)?$edit_data->shipment_add_insurance:old('shipment_add_insurance')}}">
						</div>

						<div class="panel-body">
							<label class="form-label">Discount</label>
							<input type="text" name="cal_discount" class="form-control" value="{{isset($edit_data->cal_discount)?$edit_data->cal_discount:old('cal_discount')}}">
						</div>
						
						<div class="panel-body">
							<label class="form-label">Taxes</label>
							<input type="text" name="shipment_taxes" class="form-control" value="{{isset($edit_data->shipment_taxes)?$edit_data->shipment_taxes:old('shipment_taxes')}}">
						</div>
						
						
						<div class="panel-body">
							<input type="submit" class="btn btn-success btn-add-new" value="{{isset($edit_data->id)? 'Update' : 'Save'}}">
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>	
@stop

@section('javascript')
	
@stop