@extends('theme::layouts.app')

@section('content')
<main class="Shipping-layout">
    <div class="container max-w-7xl">  
        <div style="text-align: center">  
            <form>
                <input type="hidden" id="order_id" name="order_id" value="{{$order_id}}" />
                <button type="button" class="cstm-btn disable_delivery_info" id="checkout-button">PAY WITH CARD</button></div>
            </form>
        </div>
    </div>
</main>
@endsection

@section('javascript')
<script src="https://js.stripe.com/v3/"></script>
	<script>
        var stripe = Stripe("pk_test_OnSnAXn9rIwebRSnwbh49RGr00lLPsjqCA");
        var checkoutButton = document.getElementById("checkout-button");

        checkoutButton.addEventListener("click", function () {
            var order_id = $("#order_id").val();
            token = "{{ csrf_token() }}";
            $.ajax({
                url: "{{route('wave.checkoutSession')}}",
                method: "POST", 
                data:{"_token": token,"order_id":order_id,"action":"stripe"},
                beforeSend: function() {
                   $(".overlay").show();
                },
                success: function(result){
                    if (result=="stripe"){
                        fetch("{{route('wave.checkoutSession')}}", {
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': token
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
                            $(".overlay").hide();
                            alert(result.error.message);
                        }
                        })
                        .catch(function (error) {
                            $(".overlay").hide();
                            console.error("Error:", error);
                        });
                    }
                },
                error: function(){
                    $(".overlay").hide();
                }
            });
        });
    </script>
@endsection
