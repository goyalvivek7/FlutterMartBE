<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Api\V1', 'middleware'=>'localization'], function () {

    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {

        Route::post('social-login', 'CustomerAuthController@social_login');

      	Route::post('verify-otp', 'CustomerAuthController@verify_otp');
      	Route::post('login-register', 'CustomerAuthController@login_register');
        Route::post('profile', 'CustomerAuthController@get_profile');
      	
        Route::post('register', 'CustomerAuthController@registration');
        Route::post('login', 'CustomerAuthController@login');

        Route::post('check-phone', 'CustomerAuthController@check_phone');
        Route::post('verify-phone', 'CustomerAuthController@verify_phone');

        Route::post('check-email', 'CustomerAuthController@check_email');
        Route::post('verify-email', 'CustomerAuthController@verify_email');

        Route::post('forgot-password', 'PasswordResetController@reset_password_request');
        Route::post('verify-token', 'PasswordResetController@verify_token');
        Route::put('reset-password', 'PasswordResetController@reset_password_submit');

        Route::post('profile-update', 'CustomerAuthController@profile_update');

        Route::group(['prefix' => 'delivery-man'], function () {
            Route::post('login', 'DeliveryManLoginController@login');
        });
    });

    Route::group(['prefix' => 'delivery-man'], function () {
        Route::get('dashboard', 'DeliverymanController@dashboard');
        Route::post('login', 'DeliverymanController@login');
        Route::post('verify-otp', 'DeliverymanController@verify_otp');
        Route::post('profile', 'DeliverymanController@get_profile');
        Route::get('current-orders', 'DeliverymanController@get_current_orders');
        Route::get('all-orders', 'DeliverymanController@get_all_orders');
        Route::post('record-location-data', 'DeliverymanController@record_location_data');
        Route::get('order-delivery-history', 'DeliverymanController@get_order_history');
        Route::post('update-order-status', 'DeliverymanController@update_order_status');
        Route::put('update-payment-status', 'DeliverymanController@order_payment_status_update');
        Route::get('order-details', 'DeliverymanController@get_order_details');
        Route::get('last-location', 'DeliverymanController@get_last_location');
        Route::put('update-fcm-token', 'DeliverymanController@update_fcm_token');
        Route::get('cancel-reasons', 'DeliverymanController@cancel_reasons');
        Route::post('notifications', 'DeliverymanController@notifications');
        Route::post('available-status', 'DeliverymanController@available_status');

        Route::group(['prefix' => 'reviews', 'middleware' => ['auth:api']], function () {
            Route::get('/{delivery_man_id}', 'DeliveryManReviewController@get_reviews');
            Route::get('rating/{delivery_man_id}', 'DeliveryManReviewController@get_rating');
            Route::post('/submit', 'DeliveryManReviewController@submit_review');
        });
    });

    Route::group(['prefix' => 'config'], function () {
        Route::get('/', 'ConfigController@configuration');
        Route::get('app-version', 'ConfigController@app_version');
    });

    Route::group(['prefix' => 'products'], function () {
        Route::get('popular-search', 'ProductController@popular_search');
        Route::get('popular-product', 'ProductController@popular_product');
      	Route::get('recent-search', 'ProductController@recent_search');
        Route::post('save-search', 'ProductController@save_search');
        Route::get('latest', 'ProductController@get_latest_products');
        Route::get('discounted', 'ProductController@get_discounted_products');
        Route::get('search', 'ProductController@get_searched_products');
        Route::get('details/{id}', 'ProductController@get_product');
        Route::get('related-products/{product_id}', 'ProductController@get_related_products');
        Route::get('reviews/{product_id}', 'ProductController@get_product_reviews');
        Route::get('rating/{product_id}', 'ProductController@get_product_rating');
        Route::get('daily-needs', 'ProductController@get_daily_need_products');
        //Route::post('reviews/submit', 'ProductController@submit_product_review')->middleware('auth:api');
        Route::post('reviews/submit', 'ProductController@submit_product_review');
      	Route::get('homepagesales', 'ProductController@homepage_sales');
        Route::get('smart-deals', 'ProductController@smart_deals');
        Route::get('barcode/{barcode}', 'ProductController@barcode_product');
        Route::post('user-order-review', 'ProductController@user_order_review');
        Route::post('submit-order-review', 'ProductController@submit_order_review');
    });

    Route::group(['prefix' => 'banners'], function () {
        Route::get('/', 'BannerController@get_banners');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', 'NotificationController@get_notifications');
    });

    Route::group(['prefix' => 'complaint'], function () {
        Route::get('/issues_list', 'ComplaintController@issues_list');
        Route::post('/create', 'ComplaintController@create');
        Route::post('/complaint_list', 'ComplaintController@complaint_list');
        Route::post('/complaint_reply', 'ComplaintController@complaint_reply');
        Route::post('/complaint_detail', 'ComplaintController@complaint_detail');
    });

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', 'CategoryController@get_categories');
        Route::post('get-product-with-child-id', 'CategoryController@get_product_with_child_id');
        Route::post('get-sub-child-with-main-id', 'CategoryController@get_sub_child_with_main_id');
        Route::get('all-cat-sub-cat', 'CategoryController@all_cat_sub_cat');
        Route::get('products-with-categories', 'CategoryController@products_with_categories');
        Route::get('childes/{category_id}', 'CategoryController@get_childes');
        Route::get('products/{category_id}', 'CategoryController@get_products');
        Route::get('products/{category_id}/all', 'CategoryController@get_all_products');
        Route::post('get-product-with-cat-id', 'CategoryController@get_product_with_cat_id');
    });
  
  	Route::group(['prefix' => 'address'], function () {
      Route::get('list', 'CustomerController@address_list');
      Route::post('add', 'CustomerController@add_new_address');
      Route::put('update/{id}', 'CustomerController@update_address');
      Route::post('delete', 'CustomerController@delete_address');
    });
  
  	Route::group(['prefix' => 'order'], function () {
        Route::get('list', 'OrderController@get_order_list');
        Route::get('details', 'OrderController@get_order_details');
        Route::post('place', 'OrderController@place_order');
        Route::post('cancel', 'OrderController@cancel_order');
        Route::post('order-history', 'OrderController@order_history');
        Route::get('track', 'OrderController@track_order');
        Route::put('payment-method', 'OrderController@update_payment_method');
        Route::post('create-order', 'OrderController@create_order');
        Route::post('captured', 'OrderController@captured');
        Route::post('direct-captured', 'OrderController@direct_captured');
    });

    Route::group(['prefix' => 'wish-list'], function () {
        Route::post('/', 'WishlistController@wish_list');
        Route::post('add', 'WishlistController@add_to_wishlist');
        Route::post('remove', 'WishlistController@remove_from_wishlist');
        Route::post('check', 'WishlistController@check');
    });


    Route::group(['prefix' => 'cart'], function () {
        Route::post('add', 'CartController@add_to_cart');
        Route::post('remove', 'CartController@add_to_cart');
        Route::post('list', 'CartController@list');
        Route::post('final-cart', 'CartController@final_cart');
        Route::get('membership-package', 'CartController@membership_package');
        Route::post('create-membership-order', 'CartController@create_membership_order');
        Route::post('id-list', 'CartController@id_list');
    });

    Route::get('pages', 'TimeSlotController@all_pages');
    Route::get('page/{page_name}', 'TimeSlotController@static_pages');
    Route::get('delivery-options', 'TimeSlotController@delivery_options');
    
    //timeSlot
    Route::group(['prefix' => 'timeSlot'], function () {
        Route::get('/', 'TimeSlotController@getTime_slot');
    });

    Route::group(['prefix' => 'branch'], function () {
        Route::get('/', 'TimeSlotController@branch_list');
    });

    Route::group(['prefix' => 'customer', 'middleware' => 'auth:api'], function () {
        Route::get('info', 'CustomerController@info');
        Route::put('update-profile', 'CustomerController@update_profile');
        Route::put('cm-firebase-token', 'CustomerController@update_cm_firebase_token');

        Route::group(['prefix' => 'address'], function () {
            Route::get('list', 'CustomerController@address_list');
            Route::post('add', 'CustomerController@add_new_address');
            Route::put('update/{id}', 'CustomerController@update_address');
            Route::delete('delete', 'CustomerController@delete_address');
        });

        
        // Chatting
        Route::group(['prefix' => 'message'], function () {
            Route::get('get', 'ConversationController@messages');
            Route::post('send', 'ConversationController@messages_store');
            Route::post('chat-image', 'ConversationController@chat_image');
        });

        Route::group(['prefix' => 'wish-list'], function () {
            Route::get('/', 'WishlistController@wish_list');
            Route::post('add', 'WishlistController@add_to_wishlist');
            Route::delete('remove', 'WishlistController@remove_from_wishlist');
        });
    });

    Route::group(['prefix' => 'banners'], function () {
        Route::get('/', 'BannerController@get_banners');
    });

    // Route::group(['prefix' => 'coupon', 'middleware' => 'auth:api'], function () {
    //     Route::get('list', 'CouponController@list');
    //     Route::get('apply', 'CouponController@apply');
    // });

    Route::group(['prefix' => 'wallet'], function () {
        Route::get('user-balance', 'WalletController@user_balance');
        Route::post('create-order', 'WalletController@create_order');
        Route::post('update-order', 'WalletController@update_order');
        Route::post('wallet-histories', 'WalletController@wallet_histories');
    });

    Route::group(['prefix' => 'coupon'], function () {
        Route::get('list', 'CouponController@list');
        Route::get('apply', 'CouponController@apply');
    });

    //map api
    Route::group(['prefix' => 'mapapi'], function () {
        Route::get('place-api-autocomplete', 'MapApiController@place_api_autocomplete');
        Route::get('distance-api', 'MapApiController@distance_api');
        Route::get('place-api-details', 'MapApiController@place_api_details');
        Route::get('geocode-api', 'MapApiController@geocode_api');
    });
});
