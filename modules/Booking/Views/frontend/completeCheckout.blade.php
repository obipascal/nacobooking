@extends('layouts.app')
@section('head')
    <link href="{{ asset('module/booking/css/checkout.css?_ver='.config('app.version')) }}" rel="stylesheet">
@endsection
@section('content')
    <div class="bravo-booking-page padding-content" >
        <div class="container">
            <div id="bravo-checkout-page" >
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="form-title">{{__('Complete booking reservation payment')}}</h3>
                        <p class="line2">{{__('You can update your information in case of any mistake observation on the initial submission.')}}</p>
                         <div class="booking-form">
                             @include ($service->checkout_form_file ?? 'Booking::frontend/booking/completeCheckout-form')
                         </div>
                    </div>
                    <div class="col-md-4">
                        <div class="booking-detail">
                            @include ($service->checkout_booking_detail_file ?? '')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
@section('footer')
    <script src="{{ asset('module/booking/js/checkout.js') }}"></script>
    <script type="text/javascript">
        jQuery(function () {
            $.ajax({
                'url': bookingCore.url + '/booking/{{$booking->code}}/check-status',
                'cache': false,
                'type': 'GET',
                success: function (data) {
                    if (data.redirect !== undefined && data.redirect) {
                        window.location.href = data.redirect
                        //payWithPaystack();
                    }
                }
            });
        })
    </script>
    <form >
  <script src="https://js.paystack.co/v1/inline.js"></script>
</form>
 
<script>
 $('#startCheck').on('click', function(){
       //var method = $('#payment_gateway').val()
       var method = $('input[name="payment_gateway"]:checked').val();
       if(method === 'stripe'){
           payWithPaystack();
       }else{
           $('#completedCheck').click();
       }
       
    })



  function payWithPaystack(){
    var handler = PaystackPop.setup({
      key: 'pk_live_5c2a4ad2d65ccf37194fc12fcd1e80c85dc2413f',
      email: '<?=$user->email?>',
      amount: <?=$booking->total?>+'00',
      currency: "NGN",
      ref: ''+Math.floor((Math.random() * 1000000000) + 1), // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
      callback: function(response){
          $('#startCheck').attr('disabled','disabled').text('processing payment');
          $('#reference').val(response.reference);
          $('#completedCheck').click();
          //alert('success. transaction ref is ' + response.reference);
      },
      onClose: function(){
          alert('window closed');
      }
    });
    handler.openIframe();
  }
</script>

    
    
@endsection