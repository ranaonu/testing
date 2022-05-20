@extends('theme::layouts.app')

@section('content')

@php
	$selected_carrier = request()->segment(count(request()->segments()));
@endphp
<div class="tracking-boxes">
	<div class="container px-1 px-md-4 py-5 mx-auto">
		<h2 class="detail-page"><i class="fas fa-map-marker-alt"></i> Tracking Details</h2>
		<div class="card">
			<div class="row d-flex justify-content-between px-3 track-package">
				<div class="col-lg-6">
					<h5>ORDER <span class="text-primary font-weight-bold order_number">#{{$tracking_data['order_number']}}</span></h5>
				</div>
				<div class="col-lg-6 d-flex flex-column">
					<p class="wp-detil">Expected Arrival <span class="estimatedDeliveryDate">{{$tracking_data['estimatedDeliveryDate']}}</span> <br><span class="shipped_from">{{$tracking_data['shipped_from']}}</span> <span class="font-weight-bold"><a href="#" class="tracking_number">{{$tracking_data['tracking_number']}}</a></span></p>
				</div>
			</div>

			<!-- Add class 'active' to progress -->
			<div class="row d-flex justify-content-center">
				<div class="col-12">
					<ul id="progressbar" class="text-center">
						<li class="active step0"></li>
						<li class="active step0"></li>
						<li class="active step0"></li>
						<li class="active step0"></li>
						<li class="active step0"></li>
					</ul>
				</div>
			</div>

			<div class="row justify-content-between track-package">
				<div class="d-flex icon-content">
					<div class="d-flex flex-column">
						<p class="font-weight-bold">Order Confirmed</p>
					</div>
				</div>
				<div class="d-flex icon-content">
					<div class="d-flex flex-column">
						<p class="font-weight-bold">Order Picked Up</p>
					</div>
				</div>
				<div class="d-flex icon-content">
					<div class="d-flex flex-column">
						<p class="font-weight-bold">In Transit</p>
					</div>
				</div>
				<div class="d-flex icon-content">
					<div class="d-flex flex-column">
						<p class="font-weight-bold">Out for Delivery</p>
					</div>
				</div>
				<div class="d-flex icon-content">
					<div class="d-flex flex-column">
						<p class="font-weight-bold">Delivered</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="tracking-details">
<div class="container">
<div class="tramsit-box">
	<div class="row">
		<div class="col-md-8 order-2 order-sm-2" id="progress_details">
			<div class="transition-page">
				<div class="truck-icon">
					@if (strpos($tracking_data['latest_title'], 'Pick') !== false || strpos($tracking_data['latest_title'], 'pick') !== false) 
					    <i class="fas fa-people-carry"></i>
					@elseif(strpos($tracking_data['latest_title'], 'Transit') !== false || strpos($tracking_data['latest_title'], 'transit') !== false || strpos($tracking_data['latest_title'], 'Processed') !== false || strpos($tracking_data['latest_title'], 'processed') !== false || strpos($tracking_data['latest_title'], 'departed') !== false || strpos($tracking_data['latest_title'], 'Departed') !== false || strpos($tracking_data['latest_title'], 'Shipper') !== false || strpos($tracking_data['latest_title'], 'shipper') !== false || strpos($tracking_data['latest_title'], 'Created') !== false || strpos($tracking_data['latest_title'], 'created') !== false || strpos($tracking_data['latest_title'], 'Label') !== false || strpos($tracking_data['latest_title'], 'label') !== false || strpos($tracking_data['latest_title'], 'Origin') !== false || strpos($tracking_data['latest_title'], 'origin') !== false || strpos($tracking_data['latest_title'], 'transferred') !== false || strpos($tracking_data['latest_title'], 'Transferred') !== false || strpos($tracking_data['latest_title'], 'clearance') !== false || strpos($tracking_data['latest_title'], 'Clearance') !== false || strpos($tracking_data['latest_title'], 'Drop-Off') !== false || strpos($tracking_data['latest_title'], 'scheduled') !== false || strpos($tracking_data['latest_title'], 'Scheduled') !== false || strpos($tracking_data['latest_title'], 'At destination sort facility') !== false || strpos($tracking_data['latest_title'], 'International shipment release - Import') !== false || strpos($tracking_data['latest_title'], 'At local FedEx facility') !== false || strpos($tracking_data['latest_title'], 'In FedEx possession') !== false)
						<i class="fas fa-truck"></i>
					@elseif(strpos($tracking_data['latest_title'], 'Hold') !== false || strpos($tracking_data['latest_title'], 'hold') !== false || strpos($tracking_data['latest_title'], 'processing') !== false || strpos($tracking_data['latest_title'], 'Processing') !== false)
						<i class="fas fa-hourglass-start"></i>
					@elseif(strpos($tracking_data['latest_title'], 'Customs') !== false || strpos($tracking_data['latest_title'], 'customs') !== false)
						<i class="fas fa-box"></i>
					@elseif(strpos($tracking_data['latest_title'], 'Delay') !== false || strpos($tracking_data['latest_title'], 'delay') !== false)
						<i class="fas fa-stopwatch"></i>
					@elseif(strpos($tracking_data['latest_title'], 'Arrived') !== false || strpos($tracking_data['latest_title'], 'arrived') !== false)
						<i class="fas fa-street-view"></i>
					@elseif(strpos($tracking_data['latest_title'], 'Delivery') !== false || strpos($tracking_data['latest_title'], 'delivery') !== false)
						<i class="fas fa-motorcycle"></i>
					@elseif(strpos($tracking_data['latest_title'], 'Delivered') !== false || strpos($tracking_data['latest_title'], 'delivered') !== false)
						<i class="fas fa-check"></i>
					@elseif(strpos($tracking_data['latest_title'], 'Awaiting') !== false || strpos($tracking_data['latest_title'], 'awaiting') !== false || strpos($tracking_data['latest_title'], 'Claim') !== false || strpos($tracking_data['latest_title'], 'claim') !== false)
						<i class="fas fa-truck-pickup"></i>
					@else
						<i class="fas fa-thumbs-up"></i>
					@endif
					
				</div>
				<h2 class="tracking-heaing latest_title">{{$tracking_data['latest_title']}}</h2>
				<p class="tracking-para"><span class="latest_date">{{$tracking_data['latest_date']}}</span> at <br><span class="latest_time">{{$tracking_data['latest_time']}}</span></p>
				<!-- slider range -->
				<hr>
				<div class="row rounded top mt-5 mb-5">
					<div class="col-md-6 py-3">
						<div class="d-flex flex-column align-items start">
							<b>Shipping Address</b>
							<p class="text-justify pt-2 shipping_address">{{$tracking_data['shipping_address']}},</p>
							<p class="text-justify shipping_country">{{$tracking_data['shipping_country']}}</p>
						</div>
					</div>
					<div class="col-md-6 py-3">
						<div class="d-flex flex-column align-items ">
							<b>Receiver Address</b>
							<p class="text-justify pt-2 receiver_address">{{$tracking_data['receiver_address']}},</p>
							<p class="text-receiver_country">{{$tracking_data['receiver_country']}}</p>
						</div>
					</div>
				</div>
				<hr class=" mt-5 mb-5">
				<div class="row mt-5">
					<div class="col-md-12">
						@php
							$pickup_found = $transit_found = $hold_found = $customs_found = $delay_found = $arrived_found = $awaiting_found = $delivery_found = $delivered_found = false;
						@endphp
						@if(count($tracking_data['tracking_progress']) == 0)
							<div class="tracking-item">
								<div class="tracking-icon status-complete">
						@else
							<div class="tracking-item">
								<div class="tracking-icon status-complete">
						@endif
								<i class="fas fa-thumbs-up"></i>
							</div>
							<div class="tracking-date shipped_date">{{$tracking_data['shipped_date']}}<span class="shipped_time">{{$tracking_data['shipped_time']}}</span></div>
							<div class="tracking-content">Order Confirmed<span>Seller Confirmed your order</span></div>
						</div>
						@foreach($tracking_data['tracking_progress'] as $tracking_progress)
							@if((strpos($tracking_progress['title'], 'Pick') !== false) && (strpos($tracking_data['latest_title'], 'Pick') !== false))
								<div class="tracking-item">
									<div class="tracking-icon status-complete">
							@elseif((strpos($tracking_progress['title'], 'Transit') !== false || strpos($tracking_progress['title'], 'transit') !== false || strpos($tracking_progress['title'], 'Processed') !== false || strpos($tracking_progress['title'], 'processed') !== false || strpos($tracking_progress['title'], 'departed') !== false || strpos($tracking_progress['title'], 'Departed') !== false || strpos($tracking_progress['title'], 'label') !== false || strpos($tracking_progress['title'], 'Label') !== false || strpos($tracking_progress['title'], 'created') !== false || strpos($tracking_progress['title'], 'Created') !== false || strpos($tracking_progress['title'], 'Shipper') !== false || strpos($tracking_progress['title'], 'shipper') !== false || strpos($tracking_progress['title'], 'Origin') !== false || strpos($tracking_progress['title'], 'origin') !== false || strpos($tracking_progress['title'], 'transferred') !== false || strpos($tracking_progress['title'], 'Transferred') !== false || strpos($tracking_progress['title'], 'clearance') !== false || strpos($tracking_progress['title'], 'Clearance') !== false || strpos($tracking_progress['title'], 'Drop-Off') !== false || strpos($tracking_progress['title'], 'scheduled') !== false || strpos($tracking_progress['title'], 'Scheduled') !== false || strpos($tracking_progress['title'], 'At destination sort facility') !== false || strpos($tracking_progress['title'], 'International shipment release - Import') !== false || strpos($tracking_progress['title'], 'At local FedEx facility') !== false || strpos($tracking_progress['title'], 'In FedEx possession') !== false) 
							&& 
							(strpos($tracking_data['latest_title'], 'Transit') !== false || strpos($tracking_data['latest_title'], 'transit') !== false || strpos($tracking_data['latest_title'], 'Processed') !== false || strpos($tracking_data['latest_title'], 'processed') !== false || strpos($tracking_data['latest_title'], 'departed') !== false || strpos($tracking_data['latest_title'], 'Departed') !== false || strpos($tracking_data['latest_title'], 'Shipper') !== false || strpos($tracking_data['latest_title'], 'shipper') !== false || strpos($tracking_data['latest_title'], 'Created') !== false || strpos($tracking_data['latest_title'], 'created') !== false || strpos($tracking_data['latest_title'], 'Label') !== false || strpos($tracking_data['latest_title'], 'label') !== false || strpos($tracking_data['latest_title'], 'Origin') !== false || strpos($tracking_data['latest_title'], 'origin') !== false || strpos($tracking_data['latest_title'], 'transferred') !== false || strpos($tracking_data['latest_title'], 'Transferred') !== false || strpos($tracking_data['latest_title'], 'clearance') !== false || strpos($tracking_data['latest_title'], 'Clearance') !== false || strpos($tracking_data['latest_title'], 'Drop-Off') !== false || strpos($tracking_data['latest_title'], 'Scheduled') !== false || strpos($tracking_data['latest_title'], 'scheduled') !== false || strpos($tracking_data['latest_title'], 'At destination sort facility') !== false || strpos($tracking_data['latest_title'], 'International shipment release - Import') !== false || strpos($tracking_data['latest_title'], 'At local FedEx facility') !== false || strpos($tracking_data['latest_title'], 'In FedEx possession') !== false))
								<div class="tracking-item">
									<div class="tracking-icon status-complete">
							@elseif((strpos($tracking_progress['title'], 'hold') !== false || strpos($tracking_progress['title'], 'Hold') !== false || strpos($tracking_progress['title'], 'Processing') !== false || strpos($tracking_progress['title'], 'processing') !== false) 
							&& 
							(strpos($tracking_data['latest_title'], 'hold') !== false || strpos($tracking_data['latest_title'], 'Hold') !== false || strpos($tracking_data['latest_title'], 'Processing') !== false || strpos($tracking_data['latest_title'], 'processing') !== false))
								<div class="tracking-item">
									<div class="tracking-icon status-complete">
							@elseif((strpos($tracking_progress['title'], 'Customs') !== false || strpos($tracking_progress['title'], 'customs') !== false) 
							&& 
							(strpos($tracking_data['latest_title'], 'Customs') !== false || strpos($tracking_data['latest_title'], 'customs') !== false))
								<div class="tracking-item">
									<div class="tracking-icon status-complete">
							@elseif((strpos($tracking_progress['title'], 'Delay') !== false || strpos($tracking_progress['title'], 'delay') !== false) 
							&& 
							(strpos($tracking_data['latest_title'], 'Delay') !== false || strpos($tracking_data['latest_title'], 'delay') !== false))
								<div class="tracking-item">
									<div class="tracking-icon status-complete">
							@elseif((strpos($tracking_progress['title'], 'Arrived') !== false || strpos($tracking_progress['title'], 'arrived') !== false) 
							&& 
							(strpos($tracking_data['latest_title'], 'Arrived') !== false || strpos($tracking_data['latest_title'], 'arrived') !== false))
								<div class="tracking-item">
									<div class="tracking-icon status-complete">
							@elseif((strpos($tracking_progress['title'], 'Awaiting') !== false || strpos($tracking_progress['title'], 'awaiting') !== false || strpos($tracking_progress['title'], 'Claim') !== false || strpos($tracking_progress['title'], 'claim') !== false) 
							&& 
							(strpos($tracking_data['latest_title'], 'Awaiting') !== false || strpos($tracking_data['latest_title'], 'awaiting') !== false || strpos($tracking_data['latest_title'], 'Claim') !== false || strpos($tracking_data['latest_title'], 'claim') !== false))
								<div class="tracking-item">
									<div class="tracking-icon status-complete">
							@elseif((strpos($tracking_progress['title'], 'Out for Delivery') !== false || strpos($tracking_progress['title'], 'Out') !== false || strpos($tracking_progress['title'], 'out') !== false || strpos($tracking_progress['title'], 'Delivery') !== false || strpos($tracking_progress['title'], 'delivery') !== false) 
							&& 
							(strpos($tracking_data['latest_title'], 'Out for Delivery') !== false || strpos($tracking_data['latest_title'], 'Out') !== false || strpos($tracking_data['latest_title'], 'out') !== false || strpos($tracking_data['latest_title'], 'Delivery') !== false || strpos($tracking_data['latest_title'], 'delivery') !== false))
								<div class="tracking-item">
									<div class="tracking-icon status-complete">
							@elseif((strpos($tracking_progress['title'], 'Delivered') !== false || strpos($tracking_progress['title'], 'delivered') !== false) 
							&& 
							(strpos($tracking_data['latest_title'], 'Delivered') !== false || strpos($tracking_data['latest_title'], 'delivered') !== false))
								<div class="tracking-item">
									<div class="tracking-icon status-complete">
							@else
								<div class="tracking-item">
									<div class="tracking-icon status-complete {{$tracking_progress['title']}} ">
							@endif
									@if (strpos($tracking_progress['title'], 'Pick') !== false || strpos($tracking_progress['title'], 'pick') !== false)
										@php
											$pickup_found = true;
										@endphp
										<i class="fas fa-people-carry"></i>
									@elseif (strpos($tracking_progress['title'], 'Transit') !== false || strpos($tracking_progress['title'], 'transit') !== false || strpos($tracking_progress['title'], 'Processed') !== false || strpos($tracking_progress['title'], 'processed') !== false || strpos($tracking_progress['title'], 'departed') !== false || strpos($tracking_progress['title'], 'Departed') !== false || strpos($tracking_progress['title'], 'Shipper') !== false || strpos($tracking_progress['title'], 'shipper') !== false || strpos($tracking_progress['title'], 'Created') !== false || strpos($tracking_progress['title'], 'created') !== false || strpos($tracking_progress['title'], 'Label') !== false || strpos($tracking_progress['title'], 'label') !== false || strpos($tracking_progress['title'], 'Origin') !== false || strpos($tracking_progress['title'], 'origin') !== false || strpos($tracking_progress['title'], 'transferred') !== false || strpos($tracking_progress['title'], 'Transferred') !== false || strpos($tracking_progress['title'], 'clearance') !== false || strpos($tracking_progress['title'], 'Clearance') !== false || strpos($tracking_progress['title'], 'Drop-Off') !== false || strpos($tracking_progress['title'], 'scheduled') !== false || strpos($tracking_progress['title'], 'Scheduled') !== false || strpos($tracking_progress['title'], 'At destination sort facility') !== false || strpos($tracking_progress['title'], 'International shipment release - Import') !== false || strpos($tracking_progress['title'], 'At local FedEx facility') !== false || strpos($tracking_progress['title'], 'In FedEx possession') !== false)
										@php
											$transit_found = true;
										@endphp
										<i class="fas fa-truck"></i>
									@elseif (strpos($tracking_progress['title'], 'hold') !== false || strpos($tracking_progress['title'], 'Hold') !== false || strpos($tracking_progress['title'], 'Processing') !== false || strpos($tracking_progress['title'], 'processing') !== false)
										@php
											$hold_found = true;
										@endphp
										<i class="fas fa-hourglass-start"></i>
									@elseif (strpos($tracking_progress['title'], 'Customs') !== false || strpos($tracking_progress['title'], 'customs') !== false)
										@php
											$customs_found = true;
										@endphp
										<i class="fas fa-box"></i>
									@elseif (strpos($tracking_progress['title'], 'Delay') !== false || strpos($tracking_progress['title'], 'delay') !== false)
										@php
											$delay_found = true;
										@endphp
										<i class="fas fa-stopwatch"></i>
									@elseif (strpos($tracking_progress['title'], 'Arrived') !== false || strpos($tracking_progress['title'], 'arrived') !== false)
										@php
											$arrived_found = true;
										@endphp
										<i class="fas fa-street-view"></i>
									@elseif (strpos($tracking_progress['title'], 'Awaiting') !== false || strpos($tracking_progress['title'], 'awaiting') !== false || strpos($tracking_progress['title'], 'Claim') !== false || strpos($tracking_progress['title'], 'claim') !== false)
										@php
											$awaiting_found = true;
										@endphp
										<i class="fas fa-truck-pickup"></i>
									@elseif (strpos($tracking_progress['title'], 'Out for Delivery') !== false || strpos($tracking_progress['title'], 'Out') !== false || strpos($tracking_progress['title'], 'out') !== false || strpos($tracking_progress['title'], 'Delivery') !== false || strpos($tracking_progress['title'], 'delivery') !== false)
										@php
											$delivery_found = true;
										@endphp
										<i class="fas fa-motorcycle"></i>
									@elseif (strpos($tracking_progress['title'], 'Delivered') !== false || strpos($tracking_progress['title'], 'delivered') !== false)
										@php
											$delivered_found = true;
										@endphp
										<i class="fas fa-check"></i>
									@endif
								</div>
								@if (strpos($tracking_progress['title'], 'Pick') !== false || strpos($tracking_progress['title'], 'pick') !== false)
									<div class="tracking-date step_date">{{$tracking_progress['step_date']}}<span class="step_time">{{$tracking_progress['step_time']}}</span></div>
									<div class="tracking-content">Order Picked up<span class="title">{{$tracking_progress['title']}}</span></div>
								@elseif (strpos($tracking_progress['title'], 'Transit') !== false || strpos($tracking_progress['title'], 'transit') !== false || strpos($tracking_progress['title'], 'Processed') !== false || strpos($tracking_progress['title'], 'processed') !== false || strpos($tracking_progress['title'], 'departed') !== false || strpos($tracking_progress['title'], 'Departed') !== false || strpos($tracking_progress['title'], 'Shipper') !== false || strpos($tracking_progress['title'], 'shipper') !== false || strpos($tracking_progress['title'], 'Created') !== false || strpos($tracking_progress['title'], 'created') !== false || strpos($tracking_progress['title'], 'Label') !== false || strpos($tracking_progress['title'], 'label') !== false || strpos($tracking_progress['title'], 'Origin') !== false || strpos($tracking_progress['title'], 'origin') !== false || strpos($tracking_progress['title'], 'transferred') !== false || strpos($tracking_progress['title'], 'Transferred') !== false || strpos($tracking_progress['title'], 'clearance') !== false || strpos($tracking_progress['title'], 'Clearance') !== false || strpos($tracking_progress['title'], 'Drop-Off') !== false || strpos($tracking_progress['title'], 'scheduled') !== false || strpos($tracking_progress['title'], 'Scheduled') !== false || strpos($tracking_progress['title'], 'At destination sort facility') !== false || strpos($tracking_progress['title'], 'International shipment release - Import') !== false || strpos($tracking_progress['title'], 'At local FedEx facility') !== false || strpos($tracking_progress['title'], 'In FedEx possession') !== false)
									<div class="tracking-date step_date">{{$tracking_progress['step_date']}}<span class="step_time">{{$tracking_progress['step_time']}}</span></div>
									<div class="tracking-content">In Transit<span class="title">{{$tracking_progress['title']}}</span></div>
								@elseif (strpos($tracking_progress['title'], 'hold') !== false || strpos($tracking_progress['title'], 'Hold') !== false || strpos($tracking_progress['title'], 'Processing') !== false || strpos($tracking_progress['title'], 'processing') !== false)
									<div class="tracking-date step_date">{{$tracking_progress['step_date']}}<span class="step_time">{{$tracking_progress['step_time']}}</span></div>
									<div class="tracking-content">On Hold<span class="title">{{$tracking_progress['title']}}</span></div>
								@elseif (strpos($tracking_progress['title'], 'Customs') !== false || strpos($tracking_progress['title'], 'customs') !== false)
									<div class="tracking-date step_date">{{$tracking_progress['step_date']}}<span class="step_time">{{$tracking_progress['step_time']}}</span></div>
									<div class="tracking-content">Customs<span class="title">{{$tracking_progress['title']}}</span></div>
								@elseif (strpos($tracking_progress['title'], 'Delay') !== false || strpos($tracking_progress['title'], 'delay') !== false)
									<div class="tracking-date step_date">{{$tracking_progress['step_date']}}<span class="step_time">{{$tracking_progress['step_time']}}</span></div>
									<div class="tracking-content">Delay <span class="title">{{$tracking_progress['title']}}</span></div>
								@elseif (strpos($tracking_progress['title'], 'Arrived') !== false || strpos($tracking_progress['title'], 'arrived') !== false)
									<div class="tracking-date step_date">{{$tracking_progress['step_date']}}<span class="step_time">{{$tracking_progress['step_time']}}</span></div>
									<div class="tracking-content">Arrived<span class="title">{{$tracking_progress['title']}}</span></div>
								@elseif (strpos($tracking_progress['title'], 'Awaiting') !== false || strpos($tracking_progress['title'], 'awaiting') !== false || strpos($tracking_progress['title'], 'Claim') !== false || strpos($tracking_progress['title'], 'claim') !== false)
									<div class="tracking-date step_date">{{$tracking_progress['step_date']}}<span class="step_time">{{$tracking_progress['step_time']}}</span></div>
									<div class="tracking-content">Awaiting to pickup<span class="title">{{$tracking_progress['title']}}</span></div>
								@elseif (strpos($tracking_progress['title'], 'Out for Delivery') !== false || strpos($tracking_progress['title'], 'Out') !== false || strpos($tracking_progress['title'], 'out') !== false || strpos($tracking_progress['title'], 'Delivery') !== false || strpos($tracking_progress['title'], 'delivery') !== false)
									<div class="tracking-date step_date">{{$tracking_progress['step_date']}}<span class="step_time">{{$tracking_progress['step_time']}}</span></div>
									<div class="tracking-content">Out for Delivery<span class="title">{{$tracking_progress['title']}}</span></div>
								@elseif (strpos($tracking_progress['title'], 'Delivered') !== false || strpos($tracking_progress['title'], 'delivered') !== false)
									<div class="tracking-date step_date">{{$tracking_progress['step_date']}}<span class="step_time">{{$tracking_progress['step_time']}}</span></div>
									<div class="tracking-content">Delivered<span class="title">{{$tracking_progress['title']}}</span></div>
								@endif
							</div>
						@endforeach
						@php
							if(strpos($tracking_data['latest_title'], 'Delivered') !== false || strpos($tracking_data['latest_title'], 'delivered') !== false)
								$pickup_found = $transit_found = $hold_found = $customs_found = $delay_found = $arrived_found = $awaiting_found = $delivery_found = $delivered_found = true;
						@endphp
						@if(!$pickup_found)
						<div class="tracking-item tracking-pending">
							<div class="tracking-icon status-pending">
								<i class="fas fa-people-carry"></i>
							</div>
							<div class="tracking-date step_date"><span>--</span></div>
							<div class="tracking-content">Order Picked up<span>--</span></div>
						</div>
						@endif
						@if(!$transit_found)
							<div class="tracking-item tracking-pending">
								<div class="tracking-icon status-pending">
									<i class="fas fa-truck"></i>
								</div>
								<div class="tracking-date"><span>--</span></div>
								<div class="tracking-content">In Transit<span>--</span></div>
							</div>
						@endif
						@if(!$hold_found)
							<div class="tracking-item tracking-pending">
								<div class="tracking-icon status-pending">
									<i class="fas fa-hourglass-start"></i>
								</div>
								<div class="tracking-date"><span>--</span></div>
								<div class="tracking-content">On Hold<span>--</span></div>
							</div>
						@endif	
						@if(!$customs_found)
							<div class="tracking-item tracking-pending">
								<div class="tracking-icon status-pending">
									<i class="fas fa-box"></i>
								</div>
								<div class="tracking-date"><span>--</span></div>
								<div class="tracking-content">Customs<span>--</span></div>
							</div>
						@endif
						@if(!$delay_found)
							<div class="tracking-item tracking-pending">
								<div class="tracking-icon status-pending">
									<i class="fas fa-stopwatch"></i>
								</div>
								<div class="tracking-date"><span>--</span></div>
								<div class="tracking-content">Delay <span>--</span></div>
							</div>
						@endif
						@if(!$arrived_found)
							<div class="tracking-item tracking-pending">
								<div class="tracking-icon status-pending">
									<i class="fas fa-street-view"></i>
								</div>
								<div class="tracking-date"><span>--</span></div>
								<div class="tracking-content">Arrived<span>--</span></div>
							</div>
						@endif
						@if(!$awaiting_found)
							<div class="tracking-item tracking-pending">
								<div class="tracking-icon status-pending">
									<i class="fas fa-truck-pickup"></i>
								</div>
								<div class="tracking-date"><span>--</span></span></div>
								<div class="tracking-content">Awaiting to pickup<span>--</span></div>
							</div>
						@endif
						@if(!$delivery_found)
							<div class="tracking-item tracking-pending">
								<div class="tracking-icon status-pending">
									<i class="fas fa-motorcycle"></i>
								</div>
								<div class="tracking-date"><span>--</span></div>
								<div class="tracking-content">Out for Delivery<span>--</span></div>
							</div>
						@endif
						@if(!$delivered_found)
							<div class="tracking-item tracking-pending">
								<div class="tracking-icon status-pending">
									<i class="fas fa-check"></i>
								</div>
								<div class="tracking-date"><span>--</span></div>
								<div class="tracking-content">Delivered<span>--</span></div>
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4 order-1 order-sm-1">
			<!-- <div class="tracker-search">
				<div class="title-container">
					<h1 class="title-down">Tracking From</h1>
				</div>
				<select name="selected_carrier" id="selected_carrier" class="form-control form-select pickup_from">
					<option value="" disabled>Select Carrier</option>
					<option value="DHL" {{($selected_carrier=='DHL')?'selected':''}}>DHL</option>
					<option value="UPS" {{($selected_carrier=='UPS')?'selected':''}}>UPS</option>
					<option value="FEDEX" {{($selected_carrier=='FEDEX')?'selected':''}}>FedEx</option>
				</select>		
			</div> -->
			<div class="tracker-search">
				<div class="title-container">
					<h1 class="title-down">Tracking Number</h1>
				</div>
				<fieldset class="field-container">
					<input type="text" placeholder="Search..." class="field" id="tracking_number" />
					<div class="icons-container">
						<div class="icon-search"></div>
						<div class="icon-close">
							<div class="x-up"></div>
							<div class="x-down"></div>
						</div>
					</div>
				</fieldset>
				<div class="alert alert-danger result_not_found"></div>
			</div>
			<div class="shippment-page p-0">
				<div class="collapse-box">
					<div class="transit-details shipped_from_carrier">
						<h3>Service</h3>
						@if($tracking_data['shipped_from'] == 'DHL' || $tracking_data['shipped_from'] == 'dhl')
							<a href="https://www.dhl.com" class="shipping-links" target="_blank">{{$tracking_data['shipped_from']}} <i class="fas fa-external-link-alt"></i></a>
						@elseif($tracking_data['shipped_from'] == 'USPS' || $tracking_data['shipped_from'] == 'usps')
							<a href="https://www.usps.com" class="shipping-links" target="_blank">{{$tracking_data['shipped_from']}} <i class="fas fa-external-link-alt"></i></a>
						@elseif($tracking_data['shipped_from'] == 'FedEx' || $tracking_data['shipped_from'] == 'fedex')
							<a href="https://www.fedex.com" class="shipping-links" target="_blank">{{$tracking_data['shipped_from']}} <i class="fas fa-external-link-alt"></i></a>
						@elseif($tracking_data['shipped_from'] == 'Canada Post' || $tracking_data['shipped_from'] == 'canada post')
							<a href="https://www.canadapost-postescanada.ca" class="shipping-links" target="_blank">{{$tracking_data['shipped_from']}} <i class="fas fa-external-link-alt"></i></a>
						@else
							<a href="#" class="shipping-links" target="_blank">{{$tracking_data['shipped_from']}} <i class="fas fa-external-link-alt"></i></a>
						@endif
					</div>
					<div class="transit-details mt-5">
						<h3>Weight</h3>
						<h6 class="totalWeight">{{$tracking_data['totalWeight']}} LBS</h6>
					</div>
					<div class="transit-details mt-5">
						<h3>Tracking Number</h3>
						<h6 class="tracking_number">{{$tracking_data['tracking_number']}}</h6>
					</div>
					<hr class="mt-4 mb-4">
				</div>
			</div>
		</div>
	</div>
</div>			
@endsection
@section('javascript')
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"></script>
	<script>

		$(document).on('ready', function() {
			$('.tracker-search .field').on('focus', function() {
				$('.tracker-search').addClass('is-focus');
			});

			$('.tracker-search .field').on('blur', function() {
				// var partner = $("#selected_carrier").val();
				var tracking_number = $("#tracking_number").val();
				
				$.ajax({
		          	type: "GET",
		          	url: '/track-package/'+tracking_number,
		          	dataType: 'JSON',
		          	success: function( response ) {
		            	console.log("response::"+response);
		            	if (response.status == 'success') {
		            		var tracking_data = response.tracking_data;
		            		var partner = tracking_data.shipped_from;
		            		if (partner == 'DHL') {
								partner_url = 'https://www.dhl.com';
							}else if(partner == 'UPS'){
								partner_url = 'https://www.ups.com';
							}else if(partner == 'FEDEX'){
								partner_url = 'https://www.fedex.com';
							}else{
								partner_url = 'https://www.dhl.com';
							}
							$(".result_not_found").html("");
		            		$(".order_number").text("#"+tracking_data.order_number);
		            		$(".shipped_from").text("#"+tracking_data.shipped_from);
		            		$(".shipped_from_carrier").html("<h3>Service</h3>"+'<a href="'+partner_url+'" class="shipping-links" target="_blank">'+partner+' <i class="fas fa-external-link-alt"></i></a>');
		            		$(".shipped_from").text(tracking_data.shipped_from);
		            		$(".shipped_date").text(tracking_data.shipped_date);
		            		$(".shipped_time").text(tracking_data.shipped_time);
		            		$(".tracking_number").text(tracking_data.tracking_number);
		            		$(".shipping_address").text(tracking_data.shipping_address);
		            		$(".shipping_country").text(tracking_data.shipping_country);
		            		$(".receiver_address").text(tracking_data.receiver_address);
		            		$(".receiver_country").text(tracking_data.receiver_country);
		            		$(".totalWeight").text(tracking_data.totalWeight+" LBS");
		            		$(".estimatedDeliveryDate").text(tracking_data.estimatedDeliveryDate);
		            		$(".latest_title").text(tracking_data.latest_title);
		            		$(".latest_date").text(tracking_data.latest_date);
		            		$(".latest_time").text(tracking_data.latest_time);
							$("#progress_details").html(tracking_data.tracking_progress_html);	
						}else{
		            		$(".result_not_found").html("<p>"+response.message+"</p>");
		            	}
						$('.tracker-search').removeClass('is-focus is-type');
				    }
		        });

			});

			$('.tracker-search .field').on('keydown', function(event) {
				$('.tracker-search').addClass('is-type');
				if((event.which === 8) && $(this).val() === '') {
					$('.tracker-search').removeClass('is-type');
				}
			});
		});
	</script>
@endsection