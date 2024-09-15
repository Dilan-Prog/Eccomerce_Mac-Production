<div class="tab-pane fade show" id="v-pills-stripe" role="tabpanel"
aria-labelledby="v-pills-home-tab">
    <div class="row">
        <div class="col-xl-12 m-auto">
            <div class="wsus__payment_area">
                <form action="{{route('user.stripe.payment')}}" method="POST" id="checkout-form">
                    @csrf
                    <input type="hidden" name="stripe_token" id="stripe-token-id">
                    <div class="d-flex mt-3 mb-3" id="info-payment">
                        <h6>Tarjeta de Debido o de Credito</h6>
                        <img class="w-25" src="{{ asset('frontend/images/payment1.png') }}" alt="">
                        <img class="w-25" src="{{ asset('frontend/images/payment2.png') }}" alt="">
                        <img class="w-25" src="{{ asset('frontend/images/payment5.png') }}" alt="">
                    </div>

                    <div id="card-element" class="form-control">

                    </div>

                    <br>
                    <button class="nav-link common_btn" id="pay-btn" onclick="createToken()" type="button">Pagar Con Tarjeta</button>





                </form>

            </div>

        </div>
        <div class="text-center mt-3">
            <small>Al momento de pagar se le hara llegar un correo electronico de confirmacion de pedido.</small>
        </div>



    </div>

</div>
@php
    $stripeSetting = \App\Models\StripeSetting::first();
@endphp
@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script src="checkout.js" defer></script>
<script>
    var stripe = Stripe("{{$stripeSetting->client_id}}");
    var elements = stripe.elements();
    var cardElement = elements.create('card');
    cardElement.mount('#card-element');

    function createToken() {
            document.getElementById("pay-btn").disabled = true;
            stripe.createToken(cardElement).then(function(result) {


                if(typeof result.error != 'undefined') {
                    document.getElementById("pay-btn").disabled = false;
                    alert(result.error.message);
                }

                // creating token success
                if(typeof result.token != 'undefined') {
                    document.getElementById("stripe-token-id").value = result.token.id;
                    document.getElementById('checkout-form').submit();
                }
            });
    }
</script>
@endpush
