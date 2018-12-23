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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// List of people
Route::get('people', 'PeopleController@index');

// List certain people
Route::get('people/{id}', 'PeopleController@show');

// Update certain people
Route::put('people', 'PeopleController@update');

// Create new people
Route::post('people', 'PeopleController@store');

// Delete certain people
Route::delete('people/{id}', 'PeopleController@destroy');
