<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/api',function(){
	return "Test api";
});





Route::post('send_otp_email', 'ApiController@send_otp_email');

Route::post('login', 'ApiController@login');

Route::post('register', 'ApiController@register');
Route::match(['get','post'],'execute_query', 'ApiController@execute_query');





Route::match(['get','post'], 'send_otp', 'ApiController@send_otp');
Route::match(['get','post'], 'send_message', 'ApiController@send_message');
Route::match(['get','post'], 'verify_otp', 'ApiController@verify_otp');


Route::match(['get','post'],'send_test_notification', 'ApiController@send_test_notification');


Route::post('check_payment', 'ApiController@check_payment');

Route::post('add_wallet', 'ApiController@add_wallet');

Route::post('check_wallet', 'ApiController@check_wallet');




Route::post('social_login ', 'ApiController@social_login');
Route::post('forget_password ', 'ApiController@forget_password');
Route::post('verify_otp_forget_password ', 'ApiController@verify_otp_forget_password');




Route::group(['middleware' => 'auth.jwt'], function () {
	Route::match(['get','post'],'logout', 'ApiController@logout');
	Route::match(['get','post'],'profile', 'ApiController@profile');
	Route::post('update_profile', 'ApiController@update_profile');
	//Route::post('change_password', 'ApiController@change_password');
	//Route::get('state_city_list', 'ApiController@state_city_list');
	Route::match(['get','post'],'cmspages', 'ApiController@cmspages');
	Route::match(['get','post'],'notification_list', 'ApiController@notification_list');
	Route::match(['get','post'],'contact_us', 'ApiController@contact_us');



	Route::match(['get','post'],'home', 'ApiController@home');
	Route::match(['get','post'],'product_list', 'ApiController@product_list');
	Route::match(['get','post'],'add_remove_wishlist', 'ApiController@add_remove_wishlist');
	Route::match(['get','post'],'user_cart', 'ApiController@user_cart');
	Route::match(['get','post'],'product_details', 'ApiController@product_details');
	Route::match(['get','post'],'user_address', 'ApiController@user_address');
	Route::match(['get','post'],'place_order', 'ApiController@place_order');
	Route::match(['get','post'],'cancel_order', 'ApiController@cancel_order');
	Route::match(['get','post'],'order_list', 'ApiController@order_list');







 //    Route::match(['get','post'],'faqs', 'ApiController@faqs');
    
 //    Route::post('dgl_form_submit', 'ApiController@dgl_form_submit');

	// Route::match(['get','post'],'course', 'ApiController@home');
	// Route::match(['get','post'],'course_details', 'ApiController@course_details');
	// Route::match(['get','post'],'course_list_by_category', 'ApiController@course_list_by_category');
	// Route::match(['get','post'],'live_classes_list', 'ApiController@live_classes_list');
	// Route::match(['get','post'],'transaction_history', 'ApiController@transaction_history');


	// Route::match(['get','post'],'create_payment', 'ApiController@create_payment');

	
	// Route::match(['get','post'],'subject_list', 'ApiController@subject_list');
	// Route::match(['get','post'],'chats', 'ApiController@chats');

	// Route::match(['get','post'],'submit_chat', 'ApiController@submit_chat');

	// Route::match(['get','post'],'contents', 'ApiController@contents');
	
	// Route::match(['get','post'],'live_classes', 'ApiController@live_classes');


 //    Route::match(['get','post'],'get_chats', 'ApiController@get_chats');

 //    Route::match(['get','post'],'send_message_to_user', 'ApiController@send_message_to_user');





});



