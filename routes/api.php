<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Backend\CartController;
use App\Http\Controllers\API\Backend\CategoryController;
use App\Http\Controllers\API\Backend\CookController;
use App\Http\Controllers\API\Backend\DetailsController;
use App\Http\Controllers\API\Backend\DiscountController;
use App\Http\Controllers\API\Backend\HomeController;
use App\Http\Controllers\API\Backend\MenuController;
use App\Http\Controllers\API\Backend\ModuleController;
use App\Http\Controllers\API\Backend\NearByController;
use App\Http\Controllers\API\Backend\OrderController;
use App\Http\Controllers\API\Backend\PreBookingController;
use App\Http\Controllers\API\Backend\ProductController;
use App\Http\Controllers\API\Backend\SubCategoryController;
use App\Http\Controllers\API\Backend\VendorController;
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

Route::prefix('v1')->middleware('api')->group(function () {
    #Authentication Route's
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        ## Auth for Admin
        Route::prefix('admin')->group(function () {
            Route::post('signup', 'adminSignUp');
            Route::get('otp', 'adminGetOTP');
            Route::post('verify-otp', 'adminVerifyOTP');
            Route::post('login', 'adminLogin');
        });
        ## Auth for Staff
        Route::prefix('staff')->group(function () {
            Route::post('signup', 'staffSignUp');
            Route::post('login', 'staffLogin');
        });
        ## Auth for Customer
        Route::prefix('customer')->group(function () {
            Route::post('signup', 'customerSignUp');
            Route::get('otp', 'customerGetOTP');
            Route::post('verify-otp', 'customerVerifyOTP');
        });
        ## Auth for Rider
        Route::prefix('rider')->group(function () {
            Route::post('signup', 'riderSignUp');
            Route::get('otp', 'riderGetOTP');
            Route::post('verify-otp', 'riderVerifyOTP');
        });
        ## Logout
        Route::get('logout', 'logout');
        Route::post('test-get-otp', 'getOtp');
    });

    ## Authenticate User Only Access the Route
    Route::middleware('jwt')->group(function () {
        ## Auth for Vendor
        Route::prefix('vendor')->group(function () {
            Route::post('signup', '\App\Http\Controllers\API\Auth\AuthController@vendorSignUp');
        });
        ## Menu Route's
        Route::prefix('menu')->controller(MenuController::class)->group(function () {
            ## Cook Accessible Route's
            Route::prefix('cook')->group(function () {
                Route::get('list', 'menuListForCook');
                Route::post('create', 'menuCreateForCook');
            })->middleware('role:cook');
            ## Admin Accessible Route's
            Route::prefix('admin')->group(function () {
                Route::get('list', 'menuListForAdmin');
                Route::put('approve/{id}', 'menuApprove');
            });
            ## Customer Accessible Route's
            Route::prefix('customer')->group(function () {
                Route::get('list', 'menuListForCustomer');
            });
            ## Menu Wishlist
            Route::prefix('wishlist')->group(function () {
                Route::post('create', 'createWishlist');
                Route::get('list', 'getWishListMenu');
            });
        });

        ## Order Route's
        Route::controller(OrderController::class)->group(function () {
            #customer accessible route's
            Route::prefix('customer')->group(function () {
                Route::post('order', 'createOrder');
                Route::get('order/{id}', 'orderDetails');
                Route::get('orders/list', 'orderListForCustomer');
                Route::get('order/cancel/{id}','orderCancel');
            });
            #admin accessible route's
            Route::prefix('admin')->group(function () {
                Route::get('order', 'orderList');
                Route::get('order/{id}', 'orderDetails');
            });
        });

        ## Address and Bank Details
        Route::controller(DetailsController::class)->group(function () {
            #Address Create & Update
            Route::post('address', 'address');
            Route::put('address/{id}', 'updateAddress');
            #Bank Details Create & Update
            Route::post('bank', 'bank');
            Route::put('bank/{id}', 'updateBank');
            #Payment
            Route::post('payment', 'payment');
            Route::get('payment-list', 'paymentList');
            #Home Address List
            Route::get('/address/{id}/{guard}', 'customerAddressList');
            #set Active Address
            Route::put('/set-active/address/{address}', 'setActiveAddress');
            #Get Location
            Route::get("/get-location", "getLocation");
            Route::get("/lat-lng", "getLatLng");
        });

        Route::resource("discount", DiscountController::class)->except(['create', 'edit']);
        Route::resource("cart", CartController::class)->except(['create', 'edit', 'show']);
        Route::resource("categories", CategoryController::class)->only(['index', 'store', 'update', 'edit']);
        // Route::resource("sub-category", SubCategoryController::class)->only(['index', 'store', 'update'])->middleware('role:super-admin|admin');
        Route::resource('module', ModuleController::class)->only(['index', 'show']);
        Route::resource('cook', CookController::class)->only(['index', 'show']);
        Route::resource('product', ProductController::class);
        Route::prefix('pre-book')->controller(PreBookingController::class)->group(function () {
            Route::post('create', 'store');
        });
        // Route::controller(HomeController::class)->group(function () {
        //     Route::get('/special-offer', 'discountList');
        //     Route::get('/slot/menu', 'slotBasedMenuItemsList');
        //     Route::get('/slot/category/menu', 'slotAndCategoryMenuItemsList');
        //     Route::get('/near/vendors', 'vendorList');
        //     Route::get('/near/product', 'productList');
        //     Route::get('/near/pre-booking', 'preBookingList');
        //     Route::get('/todays-offer', 'todaysOffer');
        // });
        Route::get("available/discount", [DiscountController::class, 'discountList']);
        Route::prefix('near-by')->controller(NearByController::class)->group(function () {
            Route::get('/pre-booking', 'preBookingList');
            Route::get('/today-offer/menu', 'todayOfferMenus');
            Route::get('/slot-based/menu', 'slotBasedMenuItemsList');
            Route::get('/category-based/menu', 'slotAndCategoryMenuItemsList');
            Route::get('vendors', 'vendorList');
            Route::get('products', 'productList');
        });
        Route::prefix('vendor')->controller(VendorController::class)->group(function () {
            Route::get('list', 'index');
            Route::get('edit/{id}', 'edit');
        });
    });
});
