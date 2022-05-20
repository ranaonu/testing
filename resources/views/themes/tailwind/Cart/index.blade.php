@extends('theme::layouts.app')

@section('content')

<main class="flex-grow overflow-x-hidden">
    <main class="Shipping-layout">
	    <div class="container max-w-7xl">
    @foreach(Wave\Plan::all() as $plan)
        @if($cart_plan['Plan_id'] == $plan->plan_id)
            @php $plan_id = $plan->plan_id; @endphp
            @php $plan_ids = explode(",",$plan_id); @endphp
            @php $monthlyplan_id = $plan_ids[0]; @endphp
            @php $yearlyplan_id = $plan_ids[1]; @endphp
            @php $price = $plan->price; @endphp
            @php $plan_name =  $plan->name; @endphp
            @php $yearprice = (int)(($price*12)-(($price*12*17)/100)); @endphp
        @endif
    @endforeach
    <h1 class="max-w-md text-4xl font-extrabold text-gray-900 sm:mx-auto lg:max-w-none lg:text-5xl sm:text-center">Cart Page</h1>
    <table style="width:100%; border-top: 1px solid #000; margin: 50px 0; text-align:center;">
  <tr>
    <th>PRODUCT</th>
    <th>PRICE</th>
    <th>PEROID</th>
    <th>TOTAL</th>
  </tr>
  <tr>
    <td>{{$plan_name}} Plan</td>
    <td><span class="montly_price">${{$price}}/ per month</span><span class="yearly_price">${{$yearprice}}/ per year</span></td>
    <td>
        <div class="form-group" style="margin: 10px;">
			<select class="form-control form-select disable_delivery_info required" required name="subscription_type" id="subscription_type">
                <option data-planId="{{$yearlyplan_id}}" selected> Yearly </option>
			    <option data-planId="{{$monthlyplan_id}}" > Monthly </option>
			</select>
	    </div>
    </td>
    <td>USD <span class="montly_price">${{$price}}</span><span class="yearly_price">${{$yearprice}}</span></td>
  </tr>
</table>
    <div class="checkout_btn">
        <form id="my_radio_box">
            <div class="form-card">
                <div class="form-head">
                    <h3>CHECKOUT OPTIONS:</h3>
                </div>
                <div class="form-body">  
                    <div class="btn-wrap">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                            <button type="button" class="cstm-btn disable_delivery_info" id="checkout-button">PAY WITH CARD</button>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div id="paypal-button-container-P-8V413366LE7070711MIFDXLA"></div>
                            </div>    
                        </div>    
                    </div>    
                    </div>
                </div>
                <div class="text-center mb-3 button-tagline">Two easier way to pay</div>
            </div>    
         </form>
    </div>
</div>
</div> 
</main>
</main>
<style>
    table tbody tr {
    border: 1px solid #000;
    height: 60px;
    font-weight:600;
}
.stripe-button-el {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 600;
    background: #0069ff;
    color: #fff;
    border-radius: 5px;
    padding: 10px 30px;
}
.stripe-button-el span
{
    background: transparent;
    box-shadow: none;
}
.montly_price{
    display:none;
}
.text-center.mb-3.button-tagline{
    font-weight:600;
    font-size:18px;
}
button#checkout-button {
    width: 100%;
    padding: 14px;
}
</style>
@endsection   
@section('javascript')
<script src="https://js.stripe.com/v3/"></script>
<script src="https://www.paypal.com/sdk/js?client-id=AWoGQBDNvT50yTtA-Z-ahkrY4YpmiyYmgTU9BkBPWTYqDZEumTYyHyu8GF2stdRiSG_ltGrn9aawSM8C&vault=true&intent=subscription&disable-funding=credit,card" data-sdk-integration-source="button-factory"></script>
<script>
    paypal.Buttons({
      style: {
          shape: 'rect',
          color: 'gold',
          layout: 'horizontal',
          label: '',
          tagline: false
      },
      funding:
        {
        disallowed: [ paypal.FUNDING.CREDIT ],
      },
      createSubscription: function(data, actions) {
        return actions.subscription.create({
          /* Creates the subscription */
          plan_id: 'P-8V413366LE7070711MIFDXLA'
        });
      },
      onApprove: function(data, actions) {
        alert(data.subscriptionID); // You can add optional success message for the subscriber here
      }
  }).render('#paypal-button-container-P-8V413366LE7070711MIFDXLA'); // Renders the PayPal button

    var stripe = Stripe("pk_test_OnSnAXn9rIwebRSnwbh49RGr00lLPsjqCA");
    var checkoutButton = document.getElementById("checkout-button");

    checkoutButton.addEventListener("click", function () {
        var plan_id = $("#subscription_type").find(':selected').attr("data-planId");
        $.ajax({url: "/create-checkout-session",method: "POST", data:{"_token": "{{ csrf_token() }}","plan_id":plan_id,"action":"stripe"}, success: function(result){
            if (result=="stripe"){
                fetch("/create-checkout-session", {   
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            })
                .then(function (response) {
                console.log(response);
                return response.json();
                })
                .then(function (session) {
                return stripe.redirectToCheckout({ sessionId: session.id});
                })
                .then(function (result) {
                    console.log(result);
                
                if (result.error) {
                    alert(result.error.message);
                }
                })
                .catch(function (error) {
                console.error("Error:", error);
                });
            }
        }});
    });

    $(document).ready(function(){
        $('#subscription_type').on('change', function() {
            if(this.value == "Monthly"){
                $(".yearly_price").hide();
                $(".montly_price").show();
            }
            else{
                $(".yearly_price").show();
                $(".montly_price").hide();
            }
        });
    });
</script> 
@endsection
