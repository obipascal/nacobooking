@extends('Email::layout')
@section('content')

    <div class="b-container">
        <div class="b-panel">
            @switch($to)
                @case ('admin')
                    <h3 class="email-headline"><strong>{{__('Hello Administrator')}}</strong></h3>
                    <p>{{__('New booking has been made')}}</p>
                @break
                @case ('vendor')
                    <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$booking->vendor->nameOrEmail ?? ''])}}</strong></h3>
                    <p>{{__('Your service has new booking')}}</p>
                @break

                @case ('customer')
                    <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$booking->first_name ?? ''])}}</strong></h3>
                    <p>{{__('Thank you for booking with us. Here are your booking information:')}}</p>
                @break

            @endswitch

            @if(!empty($payment_link))
            <div class="text-center mt20">
                <a href="{{ $payment_link }}" target="_blank" class="btn btn-primary manage-booking-btn">Make Payment for your booking</a>
            </div>
            
            <br />
            <br />
            @endif
            
            @include($service->email_new_booking_file ?? '')
            

        </div>
        @include('Booking::emails.parts.panel-customer')
    </div>
@endsection
