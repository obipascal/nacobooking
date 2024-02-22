<?php
use Illuminate\Support\Facades\Route;
// Booking
Route::group(['prefix'=>config('booking.booking_route_prefix')],function(){
    Route::post('/addToCart','BookingController@addToCart');
    
    
    // complete booking payments
    Route::post('/doCheckout','BookingController@doCheckout');
    // Do booking reservation
    Route::post('/reserve', 'BookingController@doBookingReservation');
    
    
    Route::get('/confirm/{gateway}','BookingController@confirmPayment');
    Route::get('/cancel/{gateway}','BookingController@cancelPayment');
    
    
    // show booking details
    Route::get('/{code}','BookingController@detail');
    // View booking reservation details
    Route::get('/{code}/reserved','BookingController@bookingReserved');
    // complete reservation
    Route::get('/{code}/approve', 'BookingController@approveBookingReservation')->name('approve-booking');

    Route::get('/{code}/checkout','BookingController@checkout');
    
    
    Route::get('/{code}/complete-reservation','BookingController@completeCheckout');
    Route::get('/{code}/check-status','BookingController@checkStatusCheckout');

    //ical
	Route::get('/export-ical/{type}/{id}','BookingController@exportIcal')->name('booking.admin.export-ical');
    //inquiry
    Route::post('/addEnquiry','BookingController@addEnquiry');
    
    Route::get('/{code}/tester', 'BookingController@mailTester');
});
