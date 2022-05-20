<div class="p-8">

	@if(auth()->user()->stripe_id)
        @php
            $subscription = new \Wave\Http\Controllers\SettingsController;
            $cards = $subscription->get_card();
        @endphp

        @if($cards)
            <table class="min-w-full overflow-hidden divide-y divide-gray-200 rounded-lg">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase bg-gray-100">
                            Last Four Digit
                        </th>
                        <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                            card Brand
                        </th>
                        <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                            card Type
                        </th>
                        <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                            Exp Year
                        </th>
                        <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                            Exp Month
                        </th>
                        <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                            Name on Card
                        </th>
                        <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-right text-gray-500 uppercase bg-gray-100">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cards as $item)
                        <tr class="@if($loop->index%2 == 0){{ 'bg-gray-50' }}@else{{ 'bg-gray-100' }}@endif">
                            <td class="px-6 py-4 text-sm font-medium leading-5 text-gray-900 whitespace-no-wrap">
                                **** **** **** {{ $item->last4 }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                                {{ $item->brand }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                                {{ $item->funding }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                                {{ $item->exp_year }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                                {{ $item->exp_month }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium leading-5 text-right text-gray-900 whitespace-no-wrap">
                                {{ $item->name }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium leading-5 text-right whitespace-no-wrap">
                                <button target="_blank" class="mr-2 text-indigo-600 hover:underline focus:outline-none" onclick="delete_card('{{$item->id}}');">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @else
            <p>Sorry, you don't have a active card .</p>
        @endif
        <script src="https://js.stripe.com/v3/"></script>
        <div class="form-card p-5">
        <form action="/add_card" method="post" id="payment-form">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="firstName">Enter Card Details</label>
                <div id="card-element">
                    <!-- A Stripe Element will be inserted here. -->
                </div>
            </div>
        </div>
        <div id="card-errors" role="alert"></div>
        </div>
        <div class="btn-wrap text-center">
            <button type="submit" id="complete-orders" class="cstm-btn disable_delivery_info">Add Card</button>
        </div>
        </form>

        <script>
        // Create a Stripe client.
        var stripe = Stripe('pk_test_OnSnAXn9rIwebRSnwbh49RGr00lLPsjqCA');

        // Create an instance of Elements.
        var elements = stripe.elements();

        // Custom styling can be passed to options when creating an Element.
        // (Note that this demo uses a wider set of styles than the guide below.)
        var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
        };

        // Create an instance of the card Element.
        var card = elements.create('card', 
        {
        style: style,
        hidePostalCode:true
        });

        // Add an instance of the card Element into the `card-element` <div>.
        card.mount('#card-element');

        // Handle real-time validation errors from the card Element.
        card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
        });

        // Handle form submission.
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
        event.preventDefault();

        var options = {
            name: "{{auth()->user()->name}}"
        };

        stripe.createToken(card, options).then(function(result) {
            if (result.error) {
                // Inform the user if there was an error.
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
                document.getElementById('card-city').disable = false;
            } else {
                // Send the token to your server.
                stripeTokenHandler(result.token);
            }
        });
        });

        // Submit the form with the token ID.
        function stripeTokenHandler(token) {
        // Insert the token ID into the form so it gets submitted to the server
        var form = document.getElementById('payment-form');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);

        // Submit the form
        form.submit();
        }
        </script>
	@else
		<p class="text-gray-600">When you subscribe to a plan, this is where you will be able to see your card.</p>
		<a href="{{ route('wave.settings', 'plans') }}" class="inline-flex self-start justify-center w-auto px-4 py-2 mt-5 text-sm font-medium text-white transition duration-150 ease-in-out border border-transparent rounded-md bg-wave-600 hover:bg-wave-500 focus:outline-none focus:border-wave-700 focus:shadow-outline-wave active:bg-wave-700">View Plans</a>
	@endif

</div>
@section('javascript')
<script>
function delete_card(card_id){
    $.ajax({
        type: "POST",
        url: '/delete_card',
        data: {'card_id':card_id},
        dataType: 'JSON',
        success: function( response ) {
            location.reload();
        }
    });
}    
</script>
@endsection