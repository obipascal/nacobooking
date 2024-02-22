<?php
use \Illuminate\Support\Facades\Route;

Route::group(['prefix'=>env('EVENT_ROUTE_PREFIX','event')],function(){
    Route::get('/','EventController@index')->name('event.search'); // Search
    Route::get('/{slug}','EventController@detail')->name('event.detail');// Detail
});

Route::group(['prefix'=>'user/'.env('EVENT_ROUTE_PREFIX','event'),'middleware' => ['auth','verified']],function(){
    Route::match(['get','post'],'/','VendorEventController@indexEvent')->name('event.vendor.index');
    Route::match(['get','post'],'/create','VendorEventController@createEvent')->name('event.vendor.create');
    Route::match(['get','post'],'/edit/{slug}','VendorEventController@editEvent')->name('event.vendor.edit');
    Route::match(['get','post'],'/del/{slug}','VendorEventController@deleteEvent')->name('event.vendor.delete');
    Route::match(['post'],'/store/{slug}','VendorEventController@store')->name('event.vendor.store');
    Route::get('bulkEdit/{id}','VendorEventController@bulkEditEvent')->name("event.vendor.bulk_edit");
    Route::get('/booking-report','VendorEventController@bookingReport')->name("event.vendor.booking_report");
    Route::get('/booking-report/bulkEdit/{id}','VendorEventController@bookingReportBulkEdit')->name("event.vendor.booking_report.bulk_edit");
});

Route::group(['prefix'=>'user/'.env('EVENT_ROUTE_PREFIX','event')],function(){
    Route::group(['prefix'=>'availability'],function(){
        Route::get('/','AvailabilityController@index')->name('event.vendor.availability.index');
        Route::get('/loadDates','AvailabilityController@loadDates')->name('event.vendor.availability.loadDates');
        Route::match(['get','post'],'/store','AvailabilityController@store')->name('event.vendor.availability.store');
    });
});
