@extends('theme::layouts.app')

@section('content')
<main class="Shipping-layout">
	<div class="container max-w-7xl">
		<div class="row shipping_information">
			<div class="col-lg-12">
				<div class="form-card">
					<div class="form-head">
						<h3>VIEW LABELS DOCUMENTS AND RECEIPT</h3>
					</div>
					<div class="form-body">
						<div class="row">
							<div class="col-lg-6">
								<div class="btn-wrap text-center">
									@if($partner == 'ups' || $partner == 'fedex')
										<a href="{{url('label/label_'.current($trackingNumberParts).'.pdf')}}" class="cstm-btn quote-btn">View Label</a>
									@else
										<a href="{{url('label/label_'.$tracking_number.'.pdf')}}" class="cstm-btn quote-btn">View Label</a>
									@endif
								</div>
							</div>
							<div class="col-lg-6">
								<div class="btn-wrap text-center">
									@if($partner == 'ups')
										<a href="{{url('invoice/invoice_'.current($trackingNumberParts).'.pdf')}}" class="cstm-btn quote-btn">View Invoice</a>
									@else
										<a href="{{url('invoice/invoice_'.$tracking_number.'.pdf')}}" class="cstm-btn quote-btn">View Invoice</a>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
@endsection
@section('javascript')
<script>
	$(document).on("click", "#label_btn a", function(){
		$("#invoice_btn").find('a').removeClass('inactiveLink');
	})
	$(document).on("click", "#invoice_btn a", function(){
		$("#receipt_btn").find('a').removeClass('inactiveLink');
	})
</script>