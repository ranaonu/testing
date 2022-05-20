@extends('theme::layouts.app')

@section('content')
<main class="Shipping-layout">
    <div class="container max-w-7xl">    
        <form action="/handle-stripe" method="POST">
            <input type="hidden" id="zts_account_client_number" name="zts_account_client_number" value="" />
            <input type="hidden" id="shipper_email" name="shipper_email" value="" />
            <input type="hidden" id="shipper_name" name="shipper_name" value="" />
            <input type="hidden" id="shipper_phone" name="shipper_phone" value="" />
            <script
                src="https://checkout.stripe.com/checkout.js" class="stripe-button" 
                data-key="pk_test_8Uj2qCqxTgmpf5ZKncGCxpZz"
                data-image="/themes/tailwind/images/logo.png"
                data-name="ZION SHIPPING Online Payment"
                data-description="Business Account for "
                data-amount="800"
                data-email=""
                data-zip-code="true">
            </script>
        </form>
    </div>
</main>
@endsection

@section('javascript')
	
@endsection
