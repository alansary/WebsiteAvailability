<?php

use Illuminate\Http\Request;

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

Route::group(['prefix' => 'v1'], function() {
	// User registration and authentication
	Route::post('login', 'UsersController@login');
	Route::post('register', 'UsersController@register');

	// URL routes
	Route::group(['prefix' => 'urls', 'middleware' => ['jwt.auth']], function() {
		Route::get('/all', 'UrlsController@getAllUserUrls');
		Route::post('/', 'UrlsController@store');
		Route::get('/', 'UrlsController@getUrlById');
		Route::delete('/', 'UrlsController@destroy');
		Route::patch('/', 'UrlsController@update');
	});
});
