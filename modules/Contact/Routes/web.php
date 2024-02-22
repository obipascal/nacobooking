<?php
use Illuminate\Support\Facades\Route;
//Contact
Route::match(['get','post'],'/contact','ContactController@index')->name("contact.index");
Route::match(['post'],'/contact/store','ContactController@store')->name("contact.store");