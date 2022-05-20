@extends('voyager::master')

@section('page_header')
    <h1 class="page-title">
        <i class="{{ 'voyager-logbook' }}"></i>
        {{ __('voyager::generic.'.('view')).' Shipment' }}

        <a href="{{route('create_shipment')}}" class="btn btn-primary">Add New Shipment</a>
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
				<div class="panel panel-bordered">
					<table id="dataTable" class="table table-hover">
                        <thead>
                            <tr>
                            	<th>#</th>
                            	<th>Date</th>
                            	<th>Invoice</th>
                            	<th>Total Price</th>
                            	<th>Shipper Name</th>
                            	<th>Consignee Countnry</th>
                            	<th>Consignee Name</th>
                            	<th>Package Weight</th>
                            	<th>Package Volume</th>
                            	<th>Delivery Option</th>
                            	<th>Action</th>
                            	<th>Track</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipping_data as $ship)
                        	<tr>
                        		<td>{{$ship->id}}</td>
                        		<td>{{ date('d-m-Y', strtotime($ship->created_at)) }}</td>
                        		<td>-</td>
                        		<td>{{$ship->shipment_price + $ship->total_pickup_fee + $ship->total_delivery_fee + $ship->shipment_add_insurance + $ship->shipment_taxes - $ship->cal_discount}}</td>
                        		<td>{{ucwords($ship->shipper_name)}}</td>
                        		<td>{{$ship->country_name}}</td>
                        		<td>{{ucwords($ship->consignee_name)}}</td>
                        		<td>{{$ship->package_weight}}</td>
                        		<td>{{$ship->package_volume}}</td>
                        		<td>{{$ship->delivery_option}}</td>
                        		<td>-</td>
                        		<td><a href="{{route('edit_shipment', $ship->id)}}">Edit</a></td>
                        	</tr>
                            @endforeach
                        </tbody>
                    </table>
				</div>
			</div>
		</div>
	</div>	
@stop

@section('javascript')
	<script>
        $(document).ready(function () {
            $('#dataTable').DataTable();
        });
    </script>
@stop