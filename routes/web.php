<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

if (!app()->environment('production')) {
	Route::prefix('dev-only')->group(function () {
		Route::get('phpinfo', 'TestController@phpinfo');
		Route::get('test', 'TestController@test');
		Route::get('pass', function() {
			return Hash::make('123123');
		});
		Route::get('token', function() {
			return hash('sha256', Str::random(80));
		});
	});
}

Route::middleware(['check.type.normal'])->group(function() {
	//region home
	Route::get('home', 'HomeController@redirectToHome');
	Route::get('/', 'HomeController@index')->name('home');
	Route::get('about-us', 'Front\PolicyController@showAboutUsView')->name('about-us.view');
	Route::get('policy/{policyName}', 'Front\PolicyController@showPolicyView')->name('policy.view');

	Route::get('lang/{locale}', 'HomeController@switchLocale')
        ->name('switch-locale')->where('locale', '^(en|vi|fr)$');

	// Authentication Routes...
	Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
	Route::post('login', [LoginController::class, 'login']);
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');
	Route::get('logout', [LoginController::class, 'logout'])->name('front.logout');

	// Registration Routes...
	Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
	Route::post('register', [RegisterController::class, 'register']);
	Route::get('forgot-password', [ForgotPasswordController::class, 'forgotView'])->name('forgot-password');
	Route::prefix('auth')->name('auth.')->group(function () {
		Route::group([
			'prefix' => '{provider}',
			'where' => [
				'userId' => '^(facebook|google)$'
			]
		], function() {
			Route::get('', [OAuthController::class, 'redirectToProvider'])->name('provider');
			Route::get('callback', [OAuthController::class, 'handleProviderCallback']);
		});
		Route::prefix('oauth')->group(function() {
			Route::post('phone', [OAuthController::class, 'updateOAuthPhoneNumber']);
		});

		Route::prefix('otp')->group(function() {
			Route::post('verify', [VerificationController::class, 'verify']);
			Route::post('resend', [VerificationController::class, 'resend']);
		});

		Route::prefix('password')->group(function() {
			Route::post('email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
			Route::post('resend', [VerificationController::class, 'sendResetLinkEmail']);
			Route::post('otp', [ForgotPasswordController::class, 'verify']);
			Route::post('reset', [ResetPasswordController::class, 'reset']);
		});

		//Route::post('verify-reset-password', 'Front\UserController@verifyUserWithOTP');
		//Route::post('verify-facebook-google', 'Front\UserController@verifyUserLoginFacebookGoogle');
	});
	//endregion

	Route::middleware(['check.status'])->prefix('profile')->group(function() {
		Route::prefix('personal-info')->group(function() {
			Route::get('/', 'Front\UserController@showProfileView')
                ->name('profile');
			Route::get('edit', 'Front\UserController@showProfileEditView')
                ->name('profile.edit');
			Route::prefix('change-password')->group(function() {
			    Route::get('/','Front\UserController@showProfileChangePasswordView')
                    ->name('profile.change-password');
			    Route::post('/', 'Front\UserController@changePassword');
            });

			Route::post('save', 'Front\UserController@saveProfile');
			Route::get('/coin-and-point', 'Front\UserController@getCoinAndPoint');
		});
        Route::prefix('payment/history')->group(function () {
            Route::get('/','Front\PaymentController@showPaymentHistoryView')
                ->name('payment.history');
            Route::get('/get', 'Front\PaymentController@getPaymentHistory');
        });
        Route::get('/order', 'Front\OrderController@showOrderListView')
            ->name('order.list');
        Route::get('/saved-product', 'Front\ProductController@showSavedProductView')
            ->name('saved-product-list');
	});

	Route::prefix('notification')->group(function() {
		Route::post('get-notification', 'Front\NotificationController@getNotificationForUser');
		Route::post('seen-notification', 'Front\NotificationController@seenNotification');
		Route::post('mark-all-notification', 'Front\NotificationController@markAllNotification');
	});

	Route::prefix('news')->group(function() {
	    Route::get('/','Front\NewsController@showNewsListView')
            ->name('news.list');
        Route::post('/get','Front\NewsController@getNewsList');
	    Route::prefix('{id}')->group(function () {
	        Route::get('/detail','Front\NewsController@showNewsDetailView')
                ->name('news.detail');
	        Route::get('/info', 'Front\NewsController@getNewsInfo');
        });

        Route::post('get-news', 'Front\NewsController@getNewsForHeader');
		// Route::post('seen-notification', 'Front\NotificationController@seenNotification');
		// Route::post('mark-all-notification', 'Front\NotificationController@markAllNotification');
	});

	Route::middleware(['check.status'])->prefix('payment')->group(function() {
		Route::prefix('deposit')->group(function() {
            Route::get('', 'Front\PaymentController@showDepositView')
                ->name('payment.deposit.view');
            Route::post('', 'Front\PaymentController@saveDepositInfo');
        });

	    Route::group([
	        'prefix' => '{type}/{tableId}/{subscriptionPriceId}',
            'where' => [
                'type' => '^(product|shop)$',
                'tableId' => '\d+'
            ]
        ], function() {
            Route::get('', 'Front\PaymentController@showPaymentView')
                ->name('payment.common.view');
            Route::post('', 'Front\PaymentController@savePaymentInfo');
        });

	    // Route::prefix('{type}')->group(function() {
     //        Route::get('', 'Front\PaymentController@showPaymentView')
        //->where('type', '^(subscription)$')->name('payment.subscription.common.view');
     //        Route::post('',
        // 'Front\PaymentController@savePaymentInfo')->where('type', '^(subscription)$');
     //    });

		// Route::get('success', 'Front\PaymentController@showPaymentSuccessView')
        //->name('payment.payment.success');
	});

	Route::middleware(['check.status'])->prefix('order')->group(function () {
//	    Route::get('/', 'Front\OrderController@showOrderListView')
//            ->name('order.list');
        Route::get('get', 'Front\OrderController@getOrderList');
        Route::prefix('{code}')->group(function() {
            Route::get('/', 'Front\OrderController@showOrderDetailView')
                ->name('order.detail');
            Route::get('info', 'Front\OrderController@getInfoOrder');
            Route::post('/cancel','Front\OrderController@cancelOrder');
            Route::post('/review','Front\OrderController@reviewOrder');
        });
    });

	Route::prefix('shop')->group(function() {
		Route::middleware(['check.status'])->get('create', 'Front\ShopController@showCreateShopView')
            ->name('shop.create');
		Route::post('save', 'Front\ShopController@saveShop');
		Route::prefix('{shopId}')->group(function() {
			Route::get('/', 'Front\ShopController@showShopView')->where('shopId', '\d+')->name('shop');
			Route::post('permission', 'Front\ShopController@getDataShopLevel');
			Route::get('info', 'Front\ShopController@getInfoShop')->where('shopId', '\d+');
			Route::middleware(['check.status'])->get('edit', 'Front\ShopController@showCreateShopView')->where('shopId', '\d+')->name('shop.edit');
			Route::post('save', 'Front\ShopController@saveShop')->where('shopId', '\d+');
			Route::post('follow', 'Front\ShopController@followShop')->where('shopId', '\d+');
            Route::get('follow', 'Front\ShopController@getListFollower')->where('shopId', '\d+');
			Route::prefix('product')->group(function() {
				Route::middleware(['check.status'])->get('/', 'Front\ProductController@showProductOfShopView')
                    ->name('shop.product');
				Route::post('info', 'Front\ProductController@getInfoProduct');
				Route::get('get', 'Front\ProductController@getProductList');
				Route::middleware(['check.status'])->get('create', 'Front\ProductController@showCreateProductView')
                    ->name('product.create');
				Route::post('save', 'Front\ProductController@saveProduct');
				Route::prefix('{code}')->group(function() {
					Route::middleware(['check.status'])->get('/', 'Front\ProductController@showCreateProductView')
                        ->name('product.edit');
					Route::middleware(['check.status'])->get('detail', 'Front\ProductController@showProductDetailOfShopView')
                        ->name('shop.product.detail');
					Route::get('payment', 'Front\ProductController@savePayment');
				});
			});
            Route::prefix('order')->group(function () {
                Route::get('/', 'Front\OrderController@showOrderListViewOfShop')
                    ->name('orderShop.list');
                Route::prefix('{code}')->group(function() {
                    Route::middleware(['check.status'])->get('/', 'Front\OrderController@showOrderDetailViewOfShop')
                        ->name('orderShop.detail');
                    Route::post('/approveAndUpdateShippingFee',
                        'Front\OrderController@approveAndUpdateShippingFeeOrder');
                    Route::post('/approveDelivery',
                        'Front\OrderController@approveDeliveryOrder');
                    Route::post('/complete',
                        'Front\OrderController@completeOrder');
                });
            });
            Route::prefix('resource')->group(function () {
                Route::middleware(['check.status'])->get('/', 'Front\ShopController@showResourceView')
                    ->name('shop.resource');
                Route::get('get', 'Front\ShopController@getShopResourceList');
                Route::post('delete', 'Front\ShopController@deleteShopResource');
                Route::post('save', 'Front\ShopController@saveShopResource');
            });

            Route::prefix('notification')->group(function () {
                Route::middleware(['check.status'])->get('/', 'Front\ShopController@showNotificationView')
                    ->name('shop.notification');
                Route::post('/save', 'Front\ShopController@saveNotification');
            });
            Route::prefix('banner')->group(function () {
                Route::middleware(['check.status'])->get('/', 'Front\ShopController@showBannerView')
                    ->name('shop.banner');
                    Route::get('get', 'Front\ShopController@getBanner');
                Route::post('/save', 'Front\ShopController@saveBanner');
                Route::post('/delete', 'Front\ShopController@deleteBanner');
            });
            Route::prefix('review')->group(function () {
                Route::get('/', 'Front\ShopController@showReviewView')
                    ->name('shop.review');
                Route::get('/get', 'Front\ShopController@getShopReviewList');
            });
            Route::prefix('upgrade')->group(function () {
                Route::get('/', 'Front\ShopController@showUpgradeView')->name('shop.upgrade');
                //     ->name('shop.review');
                // Route::get('/get', 'Front\ShopController@getShopReviewList');
            });
		});
	});

	Route::prefix('product')->middleware(['lowercase_filter'])->group(function() {
		Route::post('delete', 'Front\ProductController@deleteProduct');
		Route::get('/', 'Front\ProductController@showProductView')
            ->name('product-list');
		Route::get('get', 'Front\ProductController@getProductList')->middleware((['check_forbidden_search']));
		Route::post('report', 'Front\ProductController@reportProduct');
		Route::post('info', 'Front\ProductController@getInfoProduct');
		Route::get('add-to-cart', 'Front\ProductController@addToCart');
        Route::post('interest', 'Front\ProductController@interestProduct');
        Route::post('un-interest', 'Front\ProductController@unInterestProduct');

        Route::prefix('{code}')->group(function() {
			Route::get('detail', 'Front\ProductController@showProductDetailView')
                ->name('product.detail');
		});
	});

	Route::prefix('cart')->group(function() {
		Route::middleware(['check.status'])->get('/', 'Front\CartController@showCartView')->name('cart');
		Route::get('get', 'Front\CartController@getCartOfUser');
		Route::post('delete-order-detail', 'Front\CartController@deleteOrderDetail');
		Route::post('create-order', 'Front\CartController@createOrder');
	});


    Route::prefix('banner')->group(function() {
        Route::get('/', 'Front\BannerController@getBannerList')
            ->name('banner-list');
    });



    Route::middleware(['check.status'])->prefix('messenger')->group(function() {
		Route::get('/{userId?}/{firstContent?}', 'Front\ChatController@showChatView')
            ->where('userId', '\d+')
            ->name('chat');
		Route::post('config', 'Front\ChatController@getChatConfig');
        Route::get('/shop/info', 'Front\ChatController@getShopInfo');


        Route::prefix('conversation')->group(function() {
			Route::post('info', 'Front\ChatController@getConversationsInfo');
			Route::post('image', 'Front\ChatController@saveConversationImage');
            Route::post('file', 'Front\ChatController@saveConversationFile');
			Route::post('exists', 'Front\ChatController@checkCustomerConversationExists');
            Route::post('create', 'Front\ChatController@createConversation');


            Route::get('export', 'Front\ChatController@exportConversation');

			Route::prefix('{conversationId}')->group(function() {
				Route::get('permission', 'Front\ChatController@checkUserPermission');
				Route::post('chat-notify', 'Front\ChatController@sendNewChatNotification');
			});
		});

		Route::prefix('interest')->group(function() {
			Route::prefix('{interest}')->group(function() {
				Route::post('approve',
                    'Front\ChatController@approveInterestAndCreateConversation');
			});
		});
	});

	//Route::prefix('notification')->group(function() {
		//Route::post('get-notification', 'Front\NotificationController@getNotificationForUser');
		// Route::post('seen-notification', 'Front\NotificationController@seenNotification');
		// Route::post('mark-all-notification', 'Front\NotificationController@markAllNotification');
	//});

	//Route::match(['get', 'post'], 'category/all', 'Front\CategoryController@getAllCategory');
	Route::middleware(['lowercase_filter'])->match(['get', 'post'], 'area/all', 'Front\CommonController@getAreaList');
	Route::middleware(['lowercase_filter'])->match(['get', 'post'], 'category', 'Front\CommonController@getAllCategory');
	Route::post('thumbnail-titok', 'Front\CommonController@getThumbnailTiktok');
	// Route::prefix('public')->middleware(['lowercase_filter'])->group(function() {
	// 	Route::match(['get', 'post'], 'country', 'Front\CommonController@getCountryList');
	// 	Route::match(['get', 'post'], 'area', 'Front\CommonController@getAreaList');
	// 	Route::match(['get', 'post'], 'area-stack', 'Front\CommonController@getAreaStackList');
	// 	Route::match(['get', 'post'], 'category', 'Front\CommonController@getCategoryList');
	// });
});

Route::prefix('back')->group(function() {
	Route::get('login', 'Auth\LoginController@showBackendLoginForm')->name('back.login');
	Route::post('login', 'Auth\LoginController@login');
	Route::get('logout', 'Auth\LoginController@logout')->name('back.logout');

	Route::middleware('auth')->group(function() {
		Route::get('/', [HomeController::class, 'indexBackend'])->name('back.index');
		Route::get('{any}', [HomeController::class, 'indexBackend'])->where('any', '.*')
            ->name('back.any');
	});
});

Route::middleware('throttle:60,1')->get('time', 'HomeController@getTime')
    ->name('time');
