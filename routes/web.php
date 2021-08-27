<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('not-activated', ['as' => 'not-activated', 'uses' => function () {
    return view('error.not-activated');
}]);

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::post('user/updateSetting', 'UserController@updateSetting');
Route::get('user/profile', 'UserController@profile');
Route::post('user/showUserModal', 'UserController@showUserModal');
Route::post('user/updateUserModel', 'UserController@updateUserModel');
Route::resource('user', 'UserController');


Route::get('/orders', 'OrderController@index');
Route::post('order/ajaxGetOrder', 'OrderController@ajaxGetOrder');
Route::post('order/ajaxUpdateOrder', 'OrderController@ajaxUpdateOrder');
Route::post('order/ajaxTeescape', 'OrderController@ajaxTeescape');
Route::post('order/ajaxDelete', 'OrderController@ajaxDelete');
Route::get('order/statistic', 'OrderController@statistic');

// Design
Route::get('design/upload', 'DesignController@upload');
Route::post('design/upload', 'DesignController@uploadSubmit');
Route::post('design/ajaxUpload', 'DesignController@ajaxUpload');
Route::get('design/generate_mockup/{id}', 'DesignController@generate_mockup');
Route::get('design/create_mockup/{id}', 'DesignController@create_mockup');
Route::post('design/new_mockup', 'DesignController@new_mockup');
Route::get('design/new_mockup', 'DesignController@new_mockup');
Route::post('design/delete', 'DesignController@delete');
Route::get('design/trashed', 'DesignController@trashed');
Route::post('design/forceDelete', 'DesignController@forceDelete');
Route::post('design/restore', 'DesignController@restore');
//
Route::post('design/copy', 'DesignController@copy');
Route::post('design/paste', 'DesignController@paste');
//Route::get('design/ajaxSearchCollection', 'DesignController@ajaxSearchCollection');
Route::post('design/ajaxSearchCollection', 'DesignController@ajaxSearchCollection');
Route::post('design/ajaxAddCollections', 'DesignController@ajaxAddCollections');
Route::post('design/ajaxUpdateDesign', 'DesignController@ajaxUpdateDesign');
Route::post('design/ajaxGetDesign', 'DesignController@ajaxGetDesign');

Route::resource('design', 'DesignController');

// Mockup
Route::get('mockup', 'MockupController@index');
Route::get('mockup/index', 'MockupController@index');
Route::get('mockup/upload', 'MockupController@upload');
Route::post('mockup/upload', 'MockupController@uploadSubmit');
Route::post('mockup/delete', 'MockupController@delete');
Route::resource('mockup', 'MockupController');

// Collection
Route::post('collection/ajaxSearchDesign', 'CollectionController@ajaxSearchDesign');
Route::post('collection/ajaxAddDesigns', 'CollectionController@ajaxAddDesigns');
Route::post('collection/ajaxRemoveDesigns', 'CollectionController@ajaxRemoveDesigns');
Route::post('collection/ajaxSearchMockup', 'CollectionController@ajaxSearchMockup');
Route::post('collection/ajaxAddMockups', 'CollectionController@ajaxAddMockups');
Route::post('collection/ajaxRemoveMockups', 'CollectionController@ajaxRemoveMockups');
Route::post('collection/addCollectionExport', 'CollectionController@addCollectionExport');
Route::get('collection/delete/{id}', 'CollectionController@delete');
Route::get('collection/new_mockup', 'CollectionController@new_mockup');
Route::post('collection/new_mockup', 'CollectionController@new_mockup');
Route::get('collection/new_mockup2', 'CollectionController@new_mockup2');
Route::post('collection/new_mockup2', 'CollectionController@new_mockup2');
Route::get('collection/export', 'CollectionController@export');
Route::get('collection/etsycsv', 'CollectionController@etsycsv');
Route::resource('collection', 'CollectionController');

// Upload
Route::get('upload/upload', 'UploadController@upload');
Route::post('upload/upload', 'UploadController@uploadSubmit');

// Etsy Shop
Route::get('etsy', 'EtsyController@index');
Route::get('etsy/index', 'EtsyController@index')->name('index');
Route::post('etsy/store', 'EtsyController@store');
Route::post('etsy/feedShop', 'EtsyController@feedShop');
Route::get('etsy/delete/{id}', 'EtsyController@delete');
Route::get('etsy/{id}/listing', 'EtsyController@makemockups');
Route::get('etsy/{id}/taxonomy', 'EtsyController@getSellerTaxonomy');
Route::get('etsy/{id}/order', 'EtsyController@getOrders');
Route::get('etsy/orders', 'EtsyController@orders');
Route::post('etsy/showOrderModal', 'EtsyController@showOrderModal');
Route::post('etsy/updateOrderModel', 'EtsyController@updateOrderModel');
Route::get('etsy/{id}/connect', 'EtsyController@connect');
Route::get('etsy/connect_callback', 'EtsyController@connect_callback');
Route::get('etsy/updateShopInfo/{id}', 'EtsyController@updateShopInfo');
Route::get('etsy/{id}/edit', 'EtsyController@edit');
Route::get('etsy/{id}/clone', 'EtsyController@clone');
Route::get('etsy/{id}/product', 'EtsyController@getProducts');

Route::get('etsy/{id}/listings', 'EtsyController@findAllShopListingsActive');

Route::put('etsy/update', 'EtsyController@update');
Route::post('etsy/uploadListingImage', 'EtsyController@uploadListingImagesFromShop');
Route::post('etsy/deleteListing', 'EtsyController@deleteListing');
Route::post('etsy/createListingDigital', 'EtsyController@createListingDigital');
Route::post('etsy/createListing', 'EtsyController@createListing');
Route::post('etsy/createListingForMug', 'EtsyController@createListingForMug');
Route::post('etsy/updateInventory', 'EtsyController@updateInventory');
Route::post('etsy/activeListing', 'EtsyController@activeListing');
Route::post('etsy/inactiveListing', 'EtsyController@inactiveListing');
Route::post('etsy/ajaxDelete', 'EtsyController@ajaxDelete');
Route::post('etsy/ajaxArchive', 'EtsyController@ajaxArchive');
Route::post('etsy/getTracking', 'EtsyController@getTracking');
Route::post('etsy/submitTracking', 'EtsyController@submitTracking');
Route::post('etsy/deleteOrder', 'EtsyController@deleteOrder');
Route::post('etsy/generate_mockup', 'EtsyController@generate_mockup');
Route::get('etsy/statistic', 'EtsyController@statistic');
Route::resource('etsy', 'EtsyController');
Route::get('users','UserController@index');