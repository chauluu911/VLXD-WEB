<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use Kreait\Firebase\Auth;

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
Route::prefix('back')->middleware('auth:api')->group(function () {
	Route::post('authorize-check', 'HomeController@authorizeCheck');
	Route::get('auth', 'HomeController@getAuthInfo');
	Route::post('change-password', 'Back\UserController@ChangePassword');

	Route::prefix('customer')->middleware('can_use_module:MODULE_CUSTOMER_MANAGEMENT')->group(function() {
        Route::prefix('{id}')->group(function() {
            Route::get('info', 'Back\UserController@getUserInfo');
            Route::post('reset-password', 'Back\UserController@resetUserPassword');
        });
        Route::post('search', 'Back\UserController@getUserList')->middleware('lowercase_filter');
        Route::post('save', 'Back\UserController@saveUser');
        Route::post('edit-balance', 'Back\UserController@editBalance');
        Route::post('delete', 'Back\UserController@deleteUser');
        Route::post('approve', 'Back\UserController@approveUser');

    });

    Route::prefix('shop')->middleware('can_use_module:MODULE_CUSTOMER_MANAGEMENT')->group(function() {
        Route::post('area', 'Back\AreaController@getAreaList');
        Route::prefix('{shopId}')->group(function() {
            Route::get('info', 'Back\ShopController@getShopInfo');
            Route::prefix('order')->group(function() {
                Route::post('', 'Back\ShopController@getOrderList');
                Route::get('detail/{id}', 'Back\ShopController@getOrderDetail');
                Route::post('delete', 'Back\ShopController@deleteOrder');
            });
            Route::prefix('resource')->group(function() {
                Route::post('', 'Back\ShopController@getResourceList');
            });
        });
        Route::post('search', 'Back\ShopController@getShopList')->middleware('lowercase_filter');
        Route::post('save', 'Back\ShopController@saveShop');
        Route::post('delete', 'Back\ShopController@deleteShop');
        Route::post('approve', 'Back\ShopController@approveShop');
    });

	Route::prefix('payment')->middleware('can_use_module:MODULE_PAYMENT_MANAGEMENT')->group(function() {
		Route::post('search', 'Back\PaymentController@getForPaymentList')->middleware('lowercase_filter');
        Route::post('approve', 'Back\PaymentController@approvePayment')->middleware('lowercase_filter');
	});

    Route::prefix('branch')->middleware('can_use_module:MODULE_BRANCH_MANAGEMENT')->group(function() {
		Route::post('search', 'Back\BranchController@getBranchList')->middleware('lowercase_filter');
        Route::post('save', 'Back\BranchController@saveBranch');
        Route::post('delete', 'Back\BranchController@deleteBranch');
        Route::get('{id}/info', 'Back\BranchController@getBranchInfo');
	});

    Route::prefix('product-category')->middleware('can_use_module:MODULE_CATEGORY_MANAGEMENT')->group(function() {
        Route::post('search', 'Back\CategoryController@getForCategoryList')->middleware('lowercase_filter');
        Route::post('save', 'Back\CategoryController@saveCategory');
        Route::post('delete', 'Back\CategoryController@deleteCategory');
        Route::post('change-position', 'Back\CategoryController@changePosition');
        Route::post('attribute', 'Back\CategoryAttributeController@getAttribute');
        Route::prefix('{category}')->where(['category', '\d+'])->group(function() {
            Route::get('info', 'Back\CategoryController@getCategoryInfo');
        });
    });

    Route::prefix('issue-report')->middleware('can_use_module:MODULE_CATEGORY_MANAGEMENT')->group(function() {
        Route::post('search', 'Back\CategoryController@getForCategoryList')->middleware('lowercase_filter');
        Route::post('save', 'Back\CategoryController@saveCategory');
        Route::post('delete', 'Back\CategoryController@deleteCategory');
        Route::prefix('{category}')->where(['category', '\d+'])->group(function() {
            Route::get('info', 'Back\CategoryController@getCategoryInfo');
        });
    });

	Route::prefix('notification')->middleware('can_use_module:MODULE_NOTIFICATION_MANAGEMENT')->group(function() {
		Route::post('search', 'Back\NotificationController@getForNotificationScheduleList')->middleware('lowercase_filter');
		Route::post('save', 'Back\NotificationController@saveNotificationSchedule');
		Route::post('delete', 'Back\NotificationController@deleteNotificationSchedule');

		Route::post('user', 'Back\UserController@searchCustomer')->middleware('lowercase_filter');
        Route::post('approve', 'Back\NotificationController@approveNotify');
		Route::prefix('{notificationSchedule}')->where(['notificationSchedule', '\d+'])->group(function() {
			Route::get('info', 'Back\NotificationController@getNotificationScheduleInfo');
		});
	});

    Route::prefix('employee')->middleware('can_use_module:MODULE_EMPLOYEE_MANAGEMENT')->group(function() {
        Route::post('permission', 'Back\AclObjectController@getPermissionList');
        Route::post('search', 'Back\UserController@getUserList')->middleware('lowercase_filter');
        Route::post('save', 'Back\UserController@saveUser');
        Route::get('{id}/info', 'Back\UserController@getUserInfo');
        Route::post('delete', 'Back\UserController@deleteUser');
        Route::post('{id}/reset-password', 'Back\UserController@resetUserPassword');
    });

    Route::prefix('news')->middleware('can_use_module:MODULE_NEWS_MANAGEMENT')->group(function() {
        Route::post('search', 'Back\PostController@getPostList')->middleware('lowercase_filter');
        Route::post('save', 'Back\PostController@savePost');
        Route::get('{id}/info', 'Back\PostController@getPostInfo');
        Route::post('delete', 'Back\PostController@deletePost');
    });

    Route::prefix('config')->middleware('can_use_module:MODULE_CONFIG_MANAGEMENT')->group(function() {
        Route::prefix('general')->group(function() {
            Route::prefix('wallet')->group(function() {
                Route::post('search', 'Back\ConfigController@getWalletData');
                Route::post('save', 'Back\ConfigController@saveWalletData');
            });
            Route::prefix('commission')->group(function() {
                Route::post('search', 'Back\ConfigController@getCommissionData');
                Route::post('save', 'Back\ConfigController@saveCommissionData');
            });
        });
        Route::prefix('policy')->middleware('can_use_module:MODULE_CONFIG_MANAGEMENT')->group(function() {
            Route::post('info', 'Back\ConfigController@getConfigByType');
            Route::post('save', 'Back\ConfigController@saveConfig');
        });
        Route::prefix('banner')->group(function() {
            Route::post('search', 'Back\BannerController@getBannerList');
            Route::post('save', 'Back\BannerController@saveBanner');
            Route::post('delete', 'Back\BannerController@deleteBanner');
            Route::post('approve', 'Back\BannerController@approveBanner');
            Route::post('reject', 'Back\BannerController@rejectBanner');
        });
        Route::prefix('package')->group(function() {
            Route::post('search', 'Back\SubscriptionPriceController@getPackagePushProductList');
            Route::post('save', 'Back\SubscriptionPriceController@savePackage');
            Route::post('delete', 'Back\SubscriptionPriceController@deletePackage');
        });
        Route::prefix('package-upgrade-shop')->group(function() {
            Route::post('search', 'Back\SubscriptionPriceController@getPackageUpgradeShopList');
            Route::post('save', 'Back\SubscriptionPriceController@savePackage');
            Route::post('delete', 'Back\SubscriptionPriceController@deletePackage');
        });
        Route::prefix('shop-level')->group(function() {
            Route::post('search', 'Back\ShopLevelConfigController@getShopLevelConfigList');
            Route::post('save', 'Back\ShopLevelConfigController@saveShopLevelConfig');
        });
        Route::prefix('forbidden_search')->group(function() {
            Route::post('search', 'Back\ForbiddenSearchController@getForbiddenSearchList');
            Route::post('save', 'Back\ForbiddenSearchController@save');
            Route::post('delete', 'Back\ForbiddenSearchController@delete');
        });
    });

    Route::prefix('product')->middleware('can_use_module:MODULE_PRODUCT_MANAGEMENT')->group(function() {
        Route::post('search', 'Back\ProductController@getProductList')->middleware('lowercase_filter');
        Route::post('save', 'Back\ProductController@saveCategory');
        Route::post('approve-or-reject', 'Back\ProductController@approveOrRejectProduct');
        Route::post('delete', 'Back\ProductController@deleteProduct');
        Route::prefix('{productId}')->group(function() {
            Route::get('info', 'Back\ProductController@getProductInfo');
            Route::get('detail', 'Back\ProductController@getProductDetailForModal');
        });
    });

    Route::prefix('product-report')->middleware('can_use_module:MODULE_PRODUCT_MANAGEMENT')->group(function() {
        Route::post('search', 'Back\IssueReportController@getReportList')->middleware('lowercase_filter');
    });

    Route::prefix('analytics')->group(function() {
        Route::get('/', 'Back\AnalyticsController@analyticsData')->middleware('lowercase_filter');
    });

    // Route::prefix('payment')->group(function() {
    //     Route::post('search', 'Back\PaymentController@getForPaymentList')->middleware('lowercase_filter');
    //     Route::post('approve', 'Back\PaymentController@approvePayment');
    //     Route::post('delete', 'Back\PaymentController@deletePayment');
    //     Route::get('export-excel', 'Back\PaymentController@exportExcelPayment');
    // });
});
// Route::get('/shop-all', 'Back\BranchController@getAllShop');
Route::middleware(['lowercase_filter'])->match(['get', 'post'], 'area/all', 'Back\BranchController@getAreaAll');
Route::middleware(['lowercase_filter'])->match(['get', 'post'], 'province/all', 'Back\BranchController@getProvinceAll');
Route::middleware(['lowercase_filter'])->match(['get', 'post'], 'shop/all', 'Back\BranchController@getShopAll');

