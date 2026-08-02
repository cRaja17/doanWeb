<?php

use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:api')->prefix('admin')->group(function () {

    // lấy danh mục bài viết
    Route::get('/get-categories', 'App\Http\Controllers\Api\AppController@getCategories')->name('admin.api.getCategories');
    Route::get('/get-posts', 'App\Http\Controllers\Api\AppController@getPosts')->name('admin.api.getPosts');
    // lấy sản phẩm 
    Route::get('/get-products', 'App\Http\Controllers\Api\AppController@getProducts')->name('admin.api.getProducts');
    Route::get('/get-getProductCategories', 'App\Http\Controllers\Api\AppController@getProductCategories')->name('admin.api.getProductCategories');

    Route::get('/getInformation', 'App\Http\Controllers\Api\AppController@getInformation')->name('admin.api.getInformation');
    // clear cache
    Route::delete('/clear-cache', 'App\Http\Controllers\Api\AppController@clearCache')->name('admin.api.clearCache');

    // check mã sản phẩm

    Route::GET('/check-product-code', 'App\Http\Controllers\Api\AppController@checkProductCode')->name('admin.api.checkProductCode');


    Route::post('/update-api-status', 'App\Http\Controllers\Api\AppController@updateStatus')->name('admin.api.updateStatus');
    Route::post('/update-api-deleteItem', 'App\Http\Controllers\Api\AppController@deleteItem')->name('admin.api.deleteItem');
    // tạo slug
    Route::post('/generate-slug', 'App\Http\Controllers\Api\AppController@generateSlug')->name('admin.api.generateSlug');

    // translate
    Route::GET('/translate', 'App\Http\Controllers\Api\AppController@translate')->name('admin.api.translate');

    Route::GET('/translateHtmlToHtml', 'App\Http\Controllers\Api\AppController@translateHtmlToHtml')->name('admin.api.translateHtmlToHtml');
    //order
    Route::GET('/get-orders', 'App\Http\Controllers\Api\AppController@getOrders')->name('admin.api.getOrders');
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
