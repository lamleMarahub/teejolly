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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Route::middleware('auth:api')->post('update-state', 'CollectionsEtsyController@update_state');
// Route::middleware('auth:api')->get('listings', 'CollectionsEtsyController@listings');

Route::middleware('auth:api')->post('update-state', 'CollectionsEtsyController@update_state');
Route::middleware('auth:api')->get('listings', 'CollectionsEtsyController@etsy');

Route::middleware('auth:api')->post('update-state-etsy', 'CollectionsEtsyController@update_state');
Route::middleware('auth:api')->get('etsy', 'CollectionsEtsyController@etsy');

// Route::middleware('auth:api')->post('update-state-ecwids', 'CollectionsEcwidController@update_state');
// Route::middleware('auth:api')->get('ecwids', 'CollectionsEcwidController@ecwids');

Route::middleware('auth:api')->post('update-state-ecwid', 'CollectionsEcwidController@update_state');
Route::middleware('auth:api')->get('ecwid', 'CollectionsEcwidController@ecwid');

Route::middleware('auth:api')->post('update-state-shopify', 'CollectionsShopifyController@update_state');
Route::middleware('auth:api')->get('shopify', 'CollectionsShopifyController@shopify');

Route::middleware('auth:api')->post('update-state-woo', 'CollectionsWooController@update_state');
Route::middleware('auth:api')->get('woo', 'CollectionsWooController@woo');

Route::middleware('auth:api')->post('get-amz-order', 'OrderController@getAmzOrder');
Route::middleware('auth:api')->post('order-edit-shipment', 'OrderController@editShipment');
Route::middleware('auth:api')->post('order-status', 'OrderController@orderStatus');