<?php

use Illuminate\Support\Facades\Route;


//////////////////////////// ADMIN ////////////////////////////
// CKFinder
Route::any('/ckfinder/connector', '\CKSource\CKFinderBridge\Controller\CKFinderController@requestAction')
    ->name('ckfinder_connector');

Route::any('/ckfinder/browser', '\CKSource\CKFinderBridge\Controller\CKFinderController@browserAction')
    ->name('ckfinder_browser');


//////////////////////////// WEB ////////////////////////////
// Webhook payOS: không đặt trong nhóm ngôn ngữ vì payOS gọi thẳng URL cố định,
// và phải nằm ngoài CSRF (xem bootstrap/app.php).
Route::post('/payos/webhook', 'App\Http\Controllers\Web\Payment\PayOSController@webhook')->name('payos.webhook');

// giỏ hàng
Route::post('/ajax_add_to_cart', 'App\Http\Controllers\Web\CartController@ajaxAddToCart')->name('ajax_add_to_cart');
Route::post('/ajax_get_cart', 'App\Http\Controllers\Web\CartController@ajaxGetCartData')->name('ajax_get_cart');
Route::post('/ajax_update_cart', 'App\Http\Controllers\Web\CartController@ajaxUpdateCart')->name('ajax_update_cart');


Route::post('/ajax_get_products', 'App\Http\Controllers\Web\ProductCategoryController@ajaxGetProducts')->name('ajax_get_products');


Route::group([
    'prefix' => \Mcamara\LaravelLocalization\Facades\LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
], function () {

    // chuyển khoản ngân hàng qua payOS
    Route::get('/checkout_bank', 'App\Http\Controllers\Web\Payment\PayOSController@checkout')->name('payment.web.bank');
    Route::get('/payos/return', 'App\Http\Controllers\Web\Payment\PayOSController@handleReturn')->name('payment.web.payos.return');
    Route::get('/payos/cancel', 'App\Http\Controllers\Web\Payment\PayOSController@cancel')->name('payment.web.payos.cancel');
    Route::get('/payos/status/{orderCode}', 'App\Http\Controllers\Web\Payment\PayOSController@status')
        ->whereNumber('orderCode')
        ->middleware('throttle:60,1')
        ->name('payment.web.payos.status');

    Route::get('paypal/success', 'App\Http\Controllers\Web\Payment\PaypalController@success')->name('payment.web.success');
    Route::get('paypal/cancel', 'App\Http\Controllers\Web\Payment\PaypalController@cancel')->name('payment.web.cancel');
    //giỏ hàng

    // thanh toán
    Route::get('/checkout', 'App\Http\Controllers\Web\OrderController@checkout')->name('payment.web.checkout');
    Route::post('/checkout', 'App\Http\Controllers\Web\OrderController@handle_checkout')->name('web_handle_checkout');
    // cảm ơn
    Route::get('/thanks', 'App\Http\Controllers\Web\OrderController@thanks')->name('payment.web.thanks');
    // cảm ơn
    Route::get('/information/{slug}', 'App\Http\Controllers\Web\InformationController@index')->name('web.information');
    // Sản phẩm
    Route::get('/', 'App\Http\Controllers\Web\PageController@index')->name('web_home');
    Route::get('/products', 'App\Http\Controllers\Web\ProductCategoryController@allProducts')->name('web_all_products');
    Route::get('/products/{slug}', 'App\Http\Controllers\Web\ProductCategoryController@index')->name('web_list_product');
    Route::get('/product/{slug}.html', 'App\Http\Controllers\Web\ProductController@index')->name('web_product_detail');

    // post_category & all posts
    Route::get('/posts', 'App\Http\Controllers\Web\PostCategoryController@allPosts')->name('web_all_posts');
    Route::get('/news', 'App\Http\Controllers\Web\PostCategoryController@allPosts')->name('web_news_all');
    Route::get('/posts/{slug}', 'App\Http\Controllers\Web\PostCategoryController@index')
        ->where('slug', '^(?!admin|livewire|filament)[a-zA-Z0-9-]+$')
        ->name('web_list_post');
    Route::get('/news/{slug}.html', 'App\Http\Controllers\Web\PostController@index')
        ->where('slug', '[a-zA-Z0-9-]+')
        ->name('web_post_detail');

    // Chatbot: đặt trong nhóm localized để trợ lý biết ngôn ngữ khách đang xem.
    // Giới hạn tần suất để tránh bị lạm dụng làm cạn hạn mức API.
    Route::post('/chatbot/ajax_send', 'App\Http\Controllers\Web\ChatBotController@ajaxSend')
        ->middleware('throttle:15,1')
        ->name('ajax_chatbot_send');

    Route::post('/chatbot/ajax_support', 'App\Http\Controllers\Web\ChatBotController@ajaxSupport')
        ->middleware('throttle:5,10')
        ->name('ajax_chatbot_support');
});
